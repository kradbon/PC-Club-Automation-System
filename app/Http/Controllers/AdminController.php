<?php

namespace App\Http\Controllers;

use App\Models\ClubEvent;
use App\Models\ClubOrder;
use App\Models\Tariff;
use App\Models\RoomType;
use App\Models\User;
use App\Models\PcZone;
use App\Models\Pc;
use App\Models\Booking;
use App\Models\GamingSession;
use App\Models\WalletTransaction;
use App\Models\PaymentTransaction;
use App\Models\PageContent;
use App\Models\Promotion;
use App\Models\ActivityLog;
use App\Models\ClubReview;
use App\Http\Requests\SaveTournamentRequest;
use App\Http\Requests\SaveManagedUserRequest;
use App\Models\Tournament;
use App\Services\ActivityLogger;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        $activeSessionsQuery = GamingSession::query()->where('status', 'active');
        $activePcIds = (clone $activeSessionsQuery)->whereNotNull('pc_id')->pluck('pc_id')->unique();
        $revenueForDay = function ($day): float {
            $bookingValue = Booking::query()
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereDate('starts_at', $day)
                ->sum('total_price_snapshot');
            $packValue = ClubOrder::query()
                ->where('status', 'approved')
                ->whereDate('approved_at', $day)
                ->sum('total_cents') / 100;

            return (float) $bookingValue + (float) $packValue;
        };
        $period = in_array($request->integer('period'), [7, 14, 30], true) ? $request->integer('period') : 7;
        $chartStart = today()->subDays($period - 1);
        $bookingRevenue = Booking::query()->selectRaw('DATE(starts_at) as chart_day, SUM(total_price_snapshot) as chart_total')
            ->whereIn('status', ['confirmed', 'completed'])->whereBetween('starts_at', [$chartStart, today()->endOfDay()])
            ->groupBy('chart_day')->pluck('chart_total', 'chart_day');
        $packRevenue = ClubOrder::query()->selectRaw('DATE(approved_at) as chart_day, SUM(total_cents) as chart_total')
            ->where('status', 'approved')->whereBetween('approved_at', [$chartStart, today()->endOfDay()])
            ->groupBy('chart_day')->pluck('chart_total', 'chart_day');
        $bookingActivity = Booking::query()->selectRaw('DATE(created_at) as chart_day, COUNT(*) as chart_total')
            ->whereBetween('created_at', [$chartStart, today()->endOfDay()])->groupBy('chart_day')->pluck('chart_total', 'chart_day');
        $chartDays = collect(range($period - 1, 0))->map(fn (int $offset) => today()->subDays($offset));
        $revenueChart = $chartDays->map(fn ($day) => [
            'label' => $period === 7 ? $day->format('D') : $day->format('M j'),
            'value' => round((float) ($bookingRevenue[$day->toDateString()] ?? 0) + ((float) ($packRevenue[$day->toDateString()] ?? 0) / 100), 2),
        ]);
        $bookingChart = $chartDays->map(fn ($day) => [
            'label' => $period === 7 ? $day->format('D') : $day->format('M j'),
            'value' => (int) ($bookingActivity[$day->toDateString()] ?? 0),
        ]);
        $zoneOccupancy = PcZone::query()
            ->withCount(['pcs as total_pcs_count' => fn ($query) => $query->where('is_bookable', true)])
            ->orderBy('sort_order')->get();
        foreach ($zoneOccupancy as $zone) {
            $zone->setAttribute('active_bookings_count', GamingSession::query()->where('status', 'active')
                ->whereHas('booking', fn ($query) => $query->where('pc_zone_id', $zone->id))->count());
        }

        $stats = [
            'members' => User::whereIn('role', ['customer', 'member'])->count(),
            'pending' => ClubOrder::where('status', 'pending')->count(),
            'booking_requests' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'active_sessions' => (clone $activeSessionsQuery)->count(),
            'available_pcs' => Pc::query()->where('status', 'available')->where('is_bookable', true)->whereNotIn('id', $activePcIds)->count(),
            'occupied_pcs' => $activePcIds->count(),
            'maintenance_pcs' => Pc::query()->whereIn('status', ['maintenance', 'unavailable'])->count(),
            'revenue_today' => $revenueForDay(today()),
            'food_orders' => \App\Models\FoodOrder::query()->whereIn('status', ['pending', 'accepted', 'preparing', 'ready', 'out_for_delivery'])->count(),
            'events' => ClubEvent::where('is_published', true)->count(),
            'tournaments' => Tournament::whereIn('status', ['registration', 'published'])->count(),
        ];
        $orders = ClubOrder::with(['user', 'tariff'])->latest()->paginate(15);
        $bookingQueue = Booking::with(['user', 'zone', 'pc'])->where('status', 'pending')->orderBy('starts_at')->limit(8)->get();
        $recentBookings = Booking::with(['user', 'zone', 'pc'])->latest('created_at')->limit(6)->get();
        $recentTransactions = WalletTransaction::with(['user', 'actor'])->latest('created_at')->limit(6)->get();
        $upcomingTournaments = Tournament::query()->whereIn('status', ['registration', 'published'])
            ->where(function ($query): void { $query->whereNull('starts_at')->orWhere('starts_at', '>=', now()); })
            ->orderBy('starts_at')->limit(4)->get();
        $recentActivity = ActivityLog::with('actor')->latest('created_at')->limit(6)->get();
        $maintenancePcs = Pc::with('zone')->whereIn('status', ['maintenance', 'unavailable'])->orderBy('name')->limit(6)->get();

        return view('admin.dashboard', compact(
            'stats', 'orders', 'bookingQueue', 'recentBookings', 'recentTransactions',
            'upcomingTournaments', 'recentActivity', 'maintenancePcs', 'revenueChart', 'bookingChart', 'zoneOccupancy', 'period',
        ));
    }

    public function inventory(): View
    {
        $zones = PcZone::with(['pcs' => fn ($query) => $query->orderBy('name')])->orderBy('sort_order')->get();

        return view('admin.inventory', compact('zones'));
    }

    public function users(Request $request): View
    {
        $query = User::with('wallet')->latest();
        if ($request->filled('search')) {
            $search = '%'.$request->string('search')->toString().'%';
            $query->where(fn ($users) => $users->where('name', 'like', $search)->orWhere('email', 'like', $search));
        }
        if ($request->filled('role') && in_array($request->string('role')->toString(), ['customer', 'staff', 'admin'], true)) {
            $query->where('role', $request->string('role')->toString());
        }
        if ($request->filled('membership_tier') && in_array($request->string('membership_tier')->toString(), ['standard', 'vip'], true)) {
            $query->where('membership_tier', $request->string('membership_tier')->toString());
        }

        return view('admin.users', ['users' => $query->paginate(20)->withQueryString()]);
    }

    public function createUser(): View
    {
        return view('admin.user-form', ['managedUser' => new User(['role' => 'customer', 'membership_tier' => 'standard', 'account_status' => 'active'])]);
    }

    public function storeUser(SaveManagedUserRequest $request, WalletService $wallets, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validated();
        $user = DB::transaction(function () use ($data, $wallets): User {
            $user = User::create([
                'name' => $data['name'], 'email' => strtolower($data['email']), 'password' => $data['password'],
                'role' => $data['role'], 'membership_tier' => $data['role'] === 'customer' ? $data['membership_tier'] : 'standard',
                'account_status' => $data['account_status'], 'preferred_locale' => app()->getLocale(), 'email_verified_at' => now(),
            ]);
            if ($user->role === 'customer') {
                $wallets->ensureWallet($user);
            }

            return $user;
        });
        $activity->record($request->user(), 'user.created', $user, ['role' => $user->role, 'membership_tier' => $user->membership_tier]);

        return redirect()->route('admin.users')->with('success', 'Account created successfully.');
    }

    public function editUser(User $user): View
    {
        return view('admin.user-form', ['managedUser' => $user]);
    }

    public function updateUser(SaveManagedUserRequest $request, User $user, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validated();
        abort_if($request->user()->is($user) && ($data['role'] !== 'admin' || $data['account_status'] !== 'active'), 422, 'You cannot remove or suspend your own administrator access.');
        if ($user->isAdmin() && ($data['role'] !== 'admin' || $data['account_status'] !== 'active')) {
            $activeAdmins = User::query()->whereIn('role', ['admin', 'administrator'])->where('account_status', 'active')->count();
            abort_if($activeAdmins <= 1, 422, 'Keep at least one active administrator account.');
        }

        $user->update([
            'name' => $data['name'], 'email' => strtolower($data['email']), 'role' => $data['role'],
            'membership_tier' => $data['role'] === 'customer' ? $data['membership_tier'] : 'standard',
            'account_status' => $data['account_status'],
            ...(! empty($data['password']) ? ['password' => $data['password']] : []),
        ]);
        $activity->record($request->user(), 'user.updated', $user, ['role' => $user->role, 'membership_tier' => $user->membership_tier, 'account_status' => $user->account_status]);

        return redirect()->route('admin.users')->with('success', 'Account updated successfully.');
    }

    public function deleteUser(Request $request, User $user, ActivityLogger $activity): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'You cannot delete your own administrator account.');
        if ($user->isAdmin()) {
            $activeAdmins = User::query()->whereIn('role', ['admin', 'administrator'])->where('account_status', 'active')->count();
            abort_if($activeAdmins <= 1 && $user->account_status === 'active', 422, 'Keep at least one active administrator account.');
        }
        $activity->record($request->user(), 'user.deleted', $user, ['role' => $user->role, 'membership_tier' => $user->membership_tier]);
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Account deactivated. Its club records were retained.');
    }

    public function financials(Request $request): View
    {
        $ledger = WalletTransaction::with(['user', 'actor'])->latest('created_at')->paginate(25, ['*'], 'ledger_page')->withQueryString();
        $payments = PaymentTransaction::with('user')->latest()->paginate(15, ['*'], 'payment_page')->withQueryString();

        return view('admin.financials', compact('ledger', 'payments'));
    }

    public function tournaments(): View
    {
        return view('admin.tournaments', ['tournaments' => Tournament::query()->orderByDesc('starts_at')->paginate(15)]);
    }

    public function content(): View
    {
        return view('admin.content', [
            'pages' => PageContent::query()->orderBy('slug')->get(),
            'promotions' => Promotion::query()->latest()->get(),
            'logs' => ActivityLog::with('actor')->latest('created_at')->limit(30)->get(),
        ]);
    }

    public function reviews(): View
    {
        return view('admin.reviews', ['reviews' => ClubReview::with(['user', 'moderator'])->latest('submitted_at')->paginate(25)]);
    }

    public function moderateReview(Request $request, string $locale, ClubReview $review, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:approved,rejected']]);
        $review->update([
            'status' => $data['status'], 'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
        ]);
        $activity->record($request->user(), 'review.moderated', $review, ['status' => $data['status']]);

        return back()->with('success', 'Review moderation saved.');
    }

    public function updatePage(Request $request, string $locale, PageContent $page, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validate([
            'title_en' => ['required', 'string', 'max:180'], 'title_ru' => ['nullable', 'string', 'max:180'], 'title_tj' => ['nullable', 'string', 'max:180'],
            'body_en' => ['required', 'string', 'max:20000'], 'body_ru' => ['nullable', 'string', 'max:20000'], 'body_tj' => ['nullable', 'string', 'max:20000'],
            'is_published' => ['required', 'boolean'],
        ]);
        $page->update([
            'title_translations' => array_filter(['en' => $data['title_en'], 'ru' => $data['title_ru'] ?? null, 'tj' => $data['title_tj'] ?? null]),
            'body_translations' => array_filter(['en' => $data['body_en'], 'ru' => $data['body_ru'] ?? null, 'tj' => $data['body_tj'] ?? null]),
            'is_published' => (bool) $data['is_published'], 'updated_by' => $request->user()->id,
        ]);
        $activity->record($request->user(), 'page.updated', $page);

        return back()->with('success', 'Website page updated.');
    }

    public function storePromotion(Request $request, string $locale, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:40', 'unique:promotions,code'], 'title_en' => ['required', 'string', 'max:180'],
            'description_en' => ['nullable', 'string', 'max:2000'], 'reward_type' => ['required', 'in:bonus_fixed,bonus_percent,discount_percent'],
            'reward_value' => ['required', 'numeric', 'min:0.01', 'max:10000'], 'minimum_purchase' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'], 'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ]);
        $promotion = Promotion::create([
            'code' => ! empty($data['code']) ? strtoupper($data['code']) : null,
            'title_translations' => ['en' => $data['title_en']], 'description_translations' => ['en' => $data['description_en'] ?? null],
            'reward_type' => $data['reward_type'], 'reward_value' => $data['reward_value'],
            'minimum_purchase' => $data['minimum_purchase'] ?? '0.00', 'usage_limit' => $data['usage_limit'] ?? null,
            'starts_at' => $data['starts_at'] ?? null, 'ends_at' => $data['ends_at'] ?? null,
            'is_active' => true, 'created_by' => $request->user()->id,
        ]);
        $activity->record($request->user(), 'promotion.created', $promotion);

        return back()->with('success', 'Promotion created.');
    }

    public function updatePromotion(Request $request, string $locale, Promotion $promotion, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:40', 'unique:promotions,code,'.$promotion->id],
            'title_en' => ['required', 'string', 'max:180'],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'reward_type' => ['required', 'in:bonus_fixed,bonus_percent,discount_percent'],
            'reward_value' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'minimum_purchase' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['required', 'boolean'],
        ]);
        $titles = $promotion->title_translations ?? [];
        $titles['en'] = $data['title_en'];
        $descriptions = $promotion->description_translations ?? [];
        if (filled($data['description_en'] ?? null)) {
            $descriptions['en'] = $data['description_en'];
        } else {
            unset($descriptions['en']);
        }
        $promotion->update([
            'code' => filled($data['code'] ?? null) ? strtoupper($data['code']) : null,
            'title_translations' => $titles,
            'description_translations' => $descriptions,
            'reward_type' => $data['reward_type'], 'reward_value' => $data['reward_value'],
            'minimum_purchase' => $data['minimum_purchase'] ?? '0.00', 'usage_limit' => $data['usage_limit'] ?? null,
            'starts_at' => $data['starts_at'] ?? null, 'ends_at' => $data['ends_at'] ?? null,
            'is_active' => (bool) $data['is_active'],
        ]);
        $activity->record($request->user(), 'promotion.updated', $promotion);

        return back()->with('success', 'Promotion updated.');
    }

    public function storeTournament(SaveTournamentRequest $request, string $locale, ActivityLogger $activity): RedirectResponse
    {
        $data = $this->tournamentData($request->validated());
        $data['created_by'] = $request->user()->id;
        $tournament = Tournament::create($data);
        $activity->record($request->user(), 'tournament.created', $tournament);

        return back()->with('success', 'Tournament created.');
    }

    public function updateTournament(SaveTournamentRequest $request, string $locale, Tournament $tournament, ActivityLogger $activity): RedirectResponse
    {
        $tournament->update($this->tournamentData($request->validated()));
        $activity->record($request->user(), 'tournament.updated', $tournament);

        return back()->with('success', 'Tournament updated.');
    }

    private function tournamentData(array $data): array
    {
        return [
            'slug' => $data['slug'], 'game' => $data['game'],
            'name_translations' => array_filter(['en' => $data['name_en'], 'ru' => $data['name_ru'] ?? null, 'tj' => $data['name_tj'] ?? null]),
            'description_translations' => array_filter(['en' => $data['description_en'] ?? null, 'ru' => $data['description_ru'] ?? null, 'tj' => $data['description_tj'] ?? null]),
            'prize_translations' => array_filter(['en' => $data['prize_en'] ?? null, 'ru' => $data['prize_ru'] ?? null, 'tj' => $data['prize_tj'] ?? null]),
            'format' => $data['format'], 'status' => $data['status'],
            'participant_limit' => $data['participant_limit'] ?? null, 'team_size' => $data['team_size'],
            'registration_opens_at' => $data['registration_opens_at'] ?? null,
            'registration_closes_at' => $data['registration_closes_at'] ?? null,
            'starts_at' => $data['starts_at'] ?? null, 'ends_at' => $data['ends_at'] ?? null,
            'registration_fee' => $data['registration_fee'] ?? '0.00', 'currency' => 'TJS',
        ];
    }

    public function updateUserRole(Request $request, string $locale, User $user, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', 'in:customer,staff,admin']]);
        abort_if($request->user()->is($user) && $data['role'] !== 'admin', 422, 'You cannot remove your own administrator access.');
        $previous = $user->role;
        $user->update(['role' => $data['role']]);
        $activity->record($request->user(), 'user.role_changed', $user, ['from' => $previous, 'to' => $data['role']]);

        return back()->with('success', 'User role updated.');
    }

    public function adjustUserWallet(Request $request, string $locale, User $user, WalletService $wallets, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validate([
            'bucket' => ['required', 'in:cash,bonus'], 'direction' => ['required', 'in:credit,debit'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:10000', 'regex:/^\d+(\.\d{1,2})?$/'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'idempotency_key' => ['required', 'uuid'],
        ]);
        $wallets->ensureWallet($user);
        $transaction = $wallets->adjust($user, $request->user(), $data['bucket'], $data['direction'], $data['amount'], $data['reason'], 'admin-adjustment:'.$user->id.':'.$data['idempotency_key']);
        $activity->record($request->user(), 'wallet.adjusted', $transaction, ['user_id' => $user->id, 'bucket' => $data['bucket'], 'direction' => $data['direction'], 'amount' => $data['amount'], 'reason' => $data['reason']]);

        return back()->with('success', 'Wallet ledger adjustment recorded.');
    }

    public function updateZone(Request $request, string $locale, PcZone $zone, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validate([
            'hourly_rate' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'currency' => ['required', 'in:TJS'],
            'opens_at' => ['required', 'date_format:H:i'], 'closes_at' => ['required', 'date_format:H:i'],
            'is_active' => ['required', 'boolean'],
        ]);
        $zone->update($data);
        $activity->record($request->user(), 'pc_zone.updated', $zone, $data);

        return back()->with('success', 'Gaming zone settings saved.');
    }

    public function updatePc(Request $request, string $locale, Pc $pc, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:available,maintenance,unavailable'],
            'is_bookable' => ['required', 'boolean'],
            'maintenance_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $pc->update($data);
        $activity->record($request->user(), 'pc.updated', $pc, $data);

        return back()->with('success', 'PC inventory status saved.');
    }

    public function tariffs(): View
    {
        $tariffs = Tariff::query()->orderBy('sort_order')->get();

        return view('admin.tariffs', compact('tariffs'));
    }

    public function rooms(): View
    {
        $rooms = RoomType::query()->orderBy('sort_order')->get();

        return view('admin.rooms', compact('rooms'));
    }

    public function events(): View
    {
        $events = ClubEvent::query()->latest('starts_at')->get();

        return view('admin.events', compact('events'));
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        ClubEvent::create($this->eventData($request));

        return back()->with('success', 'Event published.');
    }

    public function updateEvent(Request $request, string $locale, ClubEvent $event): RedirectResponse
    {
        $event->update($this->eventData($request));

        return back()->with('success', 'Event updated.');
    }

    public function archiveEvent(string $locale, ClubEvent $event): RedirectResponse
    {
        $event->update(['is_published' => false]);

        return back()->with('success', 'Event hidden from the public calendar.');
    }

    public function storeRoom(Request $request): RedirectResponse
    {
        RoomType::create($this->roomData($request));

        return back()->with('success', 'Room added to the lineup.');
    }

    public function updateRoom(Request $request, string $locale, RoomType $room): RedirectResponse
    {
        $room->update($this->roomData($request));

        return back()->with('success', 'Room details updated.');
    }

    public function archiveRoom(string $locale, RoomType $room): RedirectResponse
    {
        $room->update(['is_active' => false]);

        return back()->with('success', 'Room removed from the public lineup.');
    }

    public function decideOrder(Request $request, string $locale, ClubOrder $order): RedirectResponse
    {
        $data = $request->validate(['decision' => ['required', 'in:approve,reject']]);

        DB::transaction(function () use ($request, $order, $data): void {
            $lockedOrder = ClubOrder::query()->lockForUpdate()->findOrFail($order->id);
            abort_unless($lockedOrder->status === 'pending', 409, 'This order has already been processed.');

            $lockedOrder->status = $data['decision'] === 'approve' ? 'approved' : 'rejected';
            $lockedOrder->processed_by = $request->user()->id;
            $lockedOrder->approved_at = $data['decision'] === 'approve' ? now() : null;
            $lockedOrder->save();

            if ($data['decision'] === 'approve') {
                $member = User::query()->lockForUpdate()->findOrFail($lockedOrder->user_id);
                $member->increment('balance_minutes', $lockedOrder->minutes);
            }
        });

        return back()->with('success', $data['decision'] === 'approve'
            ? 'Order approved and hours credited to the member.'
            : 'Order rejected.');
    }

    public function storeTariff(Request $request): RedirectResponse
    {
        Tariff::create($this->tariffData($request));

        return back()->with('success', 'Tariff created.');
    }

    public function updateTariff(Request $request, string $locale, Tariff $tariff): RedirectResponse
    {
        $tariff->update($this->tariffData($request));

        return back()->with('success', 'Tariff updated.');
    }

    public function archiveTariff(string $locale, Tariff $tariff): RedirectResponse
    {
        $tariff->update(['is_active' => false]);

        return back()->with('success', 'Tariff removed from the price list.');
    }

    private function tariffData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'minutes' => ['required', 'integer', 'min:15', 'max:100000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'minutes' => $data['minutes'],
            'price_cents' => (int) round((float) $data['price'] * 100),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => true,
        ];
    }

    private function roomData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:40'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'player_capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'cpu' => ['required', 'string', 'max:120'],
            'gpu' => ['required', 'string', 'max:120'],
            'memory' => ['required', 'string', 'max:80'],
            'monitor' => ['required', 'string', 'max:120'],
            'monitor_hz' => ['required', 'integer', 'min:60', 'max:1000'],
            'peripherals' => ['nullable', 'string', 'max:255'],
            'hourly_rate' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        return [
            'name' => $data['name'],
            'category' => $data['category'],
            'quantity' => $data['quantity'],
            'player_capacity' => $data['player_capacity'],
            'cpu' => $data['cpu'],
            'gpu' => $data['gpu'],
            'memory' => $data['memory'],
            'monitor' => $data['monitor'],
            'monitor_hz' => $data['monitor_hz'],
            'peripherals' => $data['peripherals'] ?? null,
            'hourly_rate_cents' => (int) round((float) $data['hourly_rate'] * 100),
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => true,
        ];
    }

    private function eventData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'type' => ['required', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        return [...$data, 'is_published' => true];
    }
}
