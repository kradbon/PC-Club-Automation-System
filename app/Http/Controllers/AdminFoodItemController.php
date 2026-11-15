<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveFoodItemRequest;
use App\Models\FoodItem;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class AdminFoodItemController extends Controller
{
    public function index(): View
    {
        $items = FoodItem::query()->orderBy('category')->orderBy('sort_order')->paginate(24);

        return view('admin.food-menu', compact('items'));
    }

    public function store(SaveFoodItemRequest $request, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validated();
        $item = FoodItem::create($this->attributes($data));
        $activity->record($request->user(), 'food_item.created', $item, ['price_cents' => $item->price_cents]);

        return back()->with('success', __('dashboard.food_item_added'));
    }

    public function update(SaveFoodItemRequest $request, string $locale, FoodItem $foodItem, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validated();
        $foodItem->update($this->attributes($data, $foodItem->id));
        $activity->record($request->user(), 'food_item.updated', $foodItem, ['price_cents' => $foodItem->price_cents]);

        return back()->with('success', __('dashboard.food_item_saved'));
    }

    private function attributes(array $data, ?int $ignoreId = null): array
    {
        return [
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['slug'] ?: $data['name'], $ignoreId),
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'price_cents' => (int) round((float) $data['price'] * 100),
            'currency' => config('club.currency', 'TJS'),
            'image_url' => $data['image_url'] ?? null,
            'is_available' => (bool) ($data['is_available'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }

    private function uniqueSlug(string $value, ?int $ignoreId): string
    {
        $base = Str::slug($value) ?: 'menu-item';
        $slug = $base;
        $suffix = 2;

        $query = fn (string $candidate) => FoodItem::query()->where('slug', $candidate)
            ->when($ignoreId !== null, fn ($builder) => $builder->where('id', '<>', $ignoreId));
        while ($query($slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
