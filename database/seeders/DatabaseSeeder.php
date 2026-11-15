<?php

namespace Database\Seeders;

use App\Models\ClubEvent;
use App\Models\PageContent;
use App\Models\Pc;
use App\Models\PcZone;
use App\Models\Tariff;
use App\Models\Tournament;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(DemoAccountSeeder::class);
        $this->call(FoodMenuSeeder::class);

        $friday = now()->next(Carbon::FRIDAY)->setTime(19, 0);
        $saturday = now()->next(Carbon::SATURDAY)->setTime(14, 0);

        foreach ([
            ['title' => 'Friday Night Valorant', 'type' => 'TOURNAMENT', 'description' => 'Team up for an open Valorant tournament.', 'starts_at' => $friday, 'ends_at' => $friday->copy()->addHours(4), 'image_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=800&q=80', 'capacity' => 32],
            ['title' => 'Build & Brew', 'type' => 'COMMUNITY', 'description' => 'Talk PC builds, share ideas, and meet other members.', 'starts_at' => $saturday, 'ends_at' => $saturday->copy()->addHours(4), 'image_url' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=800&q=80', 'capacity' => 20],
        ] as $event) {
            ClubEvent::updateOrCreate(['title' => $event['title']], $event);
        }

        foreach ([
            ['name' => 'Regular', 'description' => 'A comfortable, affordable setup for everyday gaming.', 'minutes' => 60, 'price_cents' => 1250, 'sort_order' => 1],
            ['name' => 'VIP', 'description' => 'QHD clarity, 240 Hz, and premium peripherals.', 'minutes' => 60, 'price_cents' => 2250, 'sort_order' => 2],
            ['name' => 'Luxe', 'description' => 'Flagship solo setup with a 4K OLED display.', 'minutes' => 60, 'price_cents' => 4000, 'sort_order' => 3],
            ['name' => 'Streamer Room', 'description' => 'A ready-to-stream creator room with broadcast equipment.', 'minutes' => 60, 'price_cents' => 5500, 'sort_order' => 4],
            ['name' => 'Bootcamp DUST2', 'description' => 'Five matched PCs for CS2 DUST2 team practice.', 'minutes' => 60, 'price_cents' => 11250, 'sort_order' => 5],
            ['name' => 'Bootcamp MIRAGE', 'description' => 'Five matched PCs for CS2 MIRAGE team practice.', 'minutes' => 60, 'price_cents' => 11250, 'sort_order' => 6],
            ['name' => 'WINGMAN / 01', 'description' => 'Private two-player room for duo queue and co-op.', 'minutes' => 60, 'price_cents' => 4500, 'sort_order' => 7],
            ['name' => 'WINGMAN / 02', 'description' => 'Private two-player room for duo queue and co-op.', 'minutes' => 60, 'price_cents' => 4500, 'sort_order' => 8],
            ['name' => 'SOLO LUXE / 01', 'description' => 'Private top-spec room for a premium solo session.', 'minutes' => 60, 'price_cents' => 4500, 'sort_order' => 9],
            ['name' => 'SOLO LUXE / 02', 'description' => 'Private top-spec room for a premium solo session.', 'minutes' => 60, 'price_cents' => 4500, 'sort_order' => 10],
        ] as $tariff) {
            Tariff::updateOrCreate(['name' => $tariff['name']], $tariff + ['is_active' => true]);
        }

        $zones = [
            ['regular', 'Regular', 'station', '12.50', 8, 'Intel Core i5-14400F', 'NVIDIA GeForce RTX 4060', '32 GB DDR5', '24-inch FHD IPS', 180, 'Mouse, mechanical keyboard, gaming headset'],
            ['vip', 'VIP', 'station', '22.50', 4, 'AMD Ryzen 7 7800X3D', 'NVIDIA GeForce RTX 4070 SUPER', '32 GB DDR5', '27-inch QHD IPS', 240, 'Wireless mouse, premium mechanical keyboard, wireless headset'],
            ['luxe', 'Luxe', 'station', '40.00', 2, 'AMD Ryzen 9 7950X3D', 'NVIDIA GeForce RTX 4090', '64 GB DDR5', '32-inch 4K OLED', 240, 'Wireless pro mouse, enthusiast keyboard, premium headset'],
            ['streamer-room', 'Streamer Room', 'private_room', '55.00', 2, 'AMD Ryzen 9 7950X3D', 'NVIDIA GeForce RTX 4080 SUPER', '64 GB DDR5', 'Dual 27-inch QHD IPS', 240, '4K camera, broadcast microphone, mixer, stream deck'],
            ['bootcamp-dust2', 'Bootcamp DUST2', 'private_room', '112.50', 5, 'AMD Ryzen 7 7800X3D', 'NVIDIA GeForce RTX 4070 SUPER', '32 GB DDR5 per PC', '27-inch QHD IPS', 240, 'Five matched keyboard, mouse, and headset sets'],
            ['bootcamp-mirage', 'Bootcamp MIRAGE', 'private_room', '112.50', 5, 'AMD Ryzen 7 7800X3D', 'NVIDIA GeForce RTX 4070 SUPER', '32 GB DDR5 per PC', '27-inch QHD IPS', 240, 'Five matched keyboard, mouse, and headset sets'],
            ['wingman-01', 'WINGMAN / 01', 'private_room', '45.00', 2, 'AMD Ryzen 7 7800X3D', 'NVIDIA GeForce RTX 4070', '32 GB DDR5 per PC', '27-inch QHD IPS', 240, 'Two matched keyboard, mouse, and headset sets'],
            ['wingman-02', 'WINGMAN / 02', 'private_room', '45.00', 2, 'AMD Ryzen 7 7800X3D', 'NVIDIA GeForce RTX 4070', '32 GB DDR5 per PC', '27-inch QHD IPS', 240, 'Two matched keyboard, mouse, and headset sets'],
            ['solo-luxe-01', 'SOLO LUXE / 01', 'private_room', '45.00', 1, 'AMD Ryzen 9 7950X3D', 'NVIDIA GeForce RTX 4090', '64 GB DDR5', '32-inch 4K OLED', 240, 'Wireless pro mouse, premium headset, enthusiast keyboard'],
            ['solo-luxe-02', 'SOLO LUXE / 02', 'private_room', '45.00', 1, 'AMD Ryzen 9 7950X3D', 'NVIDIA GeForce RTX 4090', '64 GB DDR5', '32-inch 4K OLED', 240, 'Wireless pro mouse, premium headset, enthusiast keyboard'],
        ];

        foreach ($zones as $i => [$slug, $name, $mode, $rate, $count, $cpu, $gpu, $memory, $monitor, $hz, $peripherals]) {
            $category = match (true) {
                str_starts_with($slug, 'bootcamp') => 'Bootcamp',
                str_starts_with($slug, 'wingman') => 'WINGMAN',
                str_starts_with($slug, 'solo-luxe') => 'SOLO LUXE',
                $slug === 'streamer-room' => 'Streamer',
                default => $name,
            };
            $zone = PcZone::updateOrCreate(['slug' => $slug], [
                'category' => $category, 'booking_mode' => $mode,
                'name_translations' => ['en' => $name, 'ru' => $this->zoneName($slug, 'ru'), 'tj' => $this->zoneName($slug, 'tj')],
                'description_translations' => ['en' => $this->description($slug), 'ru' => $this->description($slug, 'ru'), 'tj' => $this->description($slug, 'tj')],
                'hourly_rate' => $rate, 'currency' => 'TJS', 'opens_at' => '10:00:00', 'closes_at' => '02:00:00',
                'minimum_booking_minutes' => 60, 'maximum_booking_minutes' => 480, 'maximum_days_ahead' => 30,
                'sort_order' => $i + 1, 'is_active' => true,
            ]);

            for ($n = 1; $n <= $count; $n++) {
                $tag = strtoupper(str_replace('-', '', $slug)).'-'.str_pad((string) $n, 2, '0', STR_PAD_LEFT);
                $devices = $this->devices($slug);
                Pc::updateOrCreate(['asset_tag' => $tag], [
                    'pc_zone_id' => $zone->id, 'name' => $name.' / '.str_pad((string) $n, 2, '0', STR_PAD_LEFT),
                    'specifications' => compact('cpu', 'gpu', 'memory', 'monitor', 'peripherals') + ['monitor_hz' => $hz] + $devices,
                    'status' => 'available', 'is_bookable' => true,
                ]);
            }
        }

        Tournament::updateOrCreate(['slug' => 'cs2-weekly-5v5'], [
            'game' => 'Counter-Strike 2', 'name_translations' => ['en' => 'CS2 Weekly 5v5', 'ru' => 'Еженедельный турнир CS2 5 на 5', 'tj' => 'Мусобиқаи ҳафтаинаи CS2 5 ба 5'],
            'description_translations' => ['en' => 'Build a five-player squad and compete for the weekly club title.', 'ru' => 'Собери команду из пяти игроков и поборись за звание чемпиона недели.', 'tj' => 'Дастаи панҷнафара соз ва барои унвони ҳафтаинаи клуб рақобат кун.'],
            'prize_translations' => ['en' => 'Prizes announced at the venue.', 'ru' => 'Призы объявим в клубе.', 'tj' => 'Ҷоизаҳо дар клуб эълон мешаванд.'], 'format' => 'single_elimination',
            'status' => 'registration', 'participant_limit' => 8, 'team_size' => 5,
            'registration_opens_at' => now()->addHour(), 'registration_closes_at' => now()->addDays(6),
            'starts_at' => now()->addDays(7)->setTime(18, 0), 'ends_at' => now()->addDays(7)->setTime(23, 0),
            'registration_fee' => '0.00', 'currency' => 'TJS', 'created_by' => null,
        ]);

        foreach ([
            ['slug' => 'about', 'title' => 'The Club', 'body' => 'A local gaming arena for competitive play, good teams, and high-performance PCs.'],
            ['slug' => 'contact', 'title' => 'Contact', 'body' => 'Add your venue address, opening hours, phone, and social links in the admin content manager.'],
        ] as $page) {
            PageContent::updateOrCreate(['slug' => $page['slug']], [
                'title_translations' => ['en' => $page['title']], 'body_translations' => ['en' => $page['body']], 'is_published' => true,
            ]);
        }
    }

    private function zoneName(string $slug, string $locale): string
    {
        $names = [
            'regular' => ['ru' => 'Стандартная зона', 'tj' => 'Минтақаи стандартӣ'],
            'vip' => ['ru' => 'VIP-зона', 'tj' => 'Минтақаи VIP'],
            'luxe' => ['ru' => 'Люкс', 'tj' => 'Люкс'],
            'streamer-room' => ['ru' => 'Стримерская комната', 'tj' => 'Ҳуҷраи стример'],
            'bootcamp-dust2' => ['ru' => 'Буткемп DUST2', 'tj' => 'Буткемпи DUST2'],
            'bootcamp-mirage' => ['ru' => 'Буткемп MIRAGE', 'tj' => 'Буткемпи MIRAGE'],
            'wingman-01' => ['ru' => 'WINGMAN / 01', 'tj' => 'WINGMAN / 01'],
            'wingman-02' => ['ru' => 'WINGMAN / 02', 'tj' => 'WINGMAN / 02'],
            'solo-luxe-01' => ['ru' => 'SOLO LUXE / 01', 'tj' => 'SOLO LUXE / 01'],
            'solo-luxe-02' => ['ru' => 'SOLO LUXE / 02', 'tj' => 'SOLO LUXE / 02'],
        ];

        return $names[$slug][$locale];
    }

    private function devices(string $slug): array
    {
        return match (true) {
            $slug === 'regular' => ['keyboard' => 'Redragon K552 mechanical keyboard', 'mouse' => 'Logitech G102 gaming mouse', 'headset' => 'HyperX Cloud Stinger 2 headset'],
            $slug === 'vip' => ['keyboard' => 'Keychron K8 Pro mechanical keyboard', 'mouse' => 'Razer DeathAdder V3 Pro wireless mouse', 'headset' => 'HyperX Cloud III Wireless headset'],
            $slug === 'luxe' || str_starts_with($slug, 'solo-luxe') => ['keyboard' => 'SteelSeries Apex Pro TKL Wireless', 'mouse' => 'Logitech G Pro X Superlight 2', 'headset' => 'Audeze Maxwell wireless headset'],
            $slug === 'streamer-room' => ['keyboard' => 'Keychron K8 Pro mechanical keyboard', 'mouse' => 'Logitech G Pro X Superlight 2', 'headset' => 'Beyerdynamic DT 770 Pro headphones', 'microphone' => 'Shure MV7 broadcast microphone', 'camera' => 'Logitech Brio 4K camera', 'streaming' => 'Elgato Stream Deck and audio mixer'],
            str_starts_with($slug, 'bootcamp') => ['keyboard' => '5 × HyperX Alloy Origins mechanical keyboard', 'mouse' => '5 × Logitech G Pro X Superlight wireless mouse', 'headset' => '5 × HyperX Cloud III gaming headset'],
            str_starts_with($slug, 'wingman') => ['keyboard' => '2 × Keychron K8 mechanical keyboard', 'mouse' => '2 × Logitech G Pro wireless mouse', 'headset' => '2 × HyperX Cloud III gaming headset'],
            default => ['keyboard' => 'HyperX Alloy Origins mechanical keyboard', 'mouse' => 'Logitech G Pro wireless gaming mouse', 'headset' => 'HyperX Cloud III gaming headset'],
        };
    }

    private function description(string $slug, string $locale = 'en'): string
    {
        if ($locale === 'ru') {
            return match ($slug) {
                'regular' => 'Удобная и доступная станция для ежедневной игры.',
                'vip' => 'Чёткий экран QHD, частота 240 Гц и премиальная периферия.',
                'luxe' => 'Флагманская игровая станция с 4K OLED-дисплеем.',
                'streamer-room' => 'Студия для стриминга с профессиональным оборудованием.',
                'bootcamp-dust2' => 'Пять одинаковых ПК для командных тренировок CS2 на DUST2.',
                'bootcamp-mirage' => 'Пять одинаковых ПК для командных тренировок CS2 на MIRAGE.',
                'wingman-01', 'wingman-02' => 'Отдельная комната для игры вдвоём и совместных матчей.',
                default => 'Отдельная комната с топовой конфигурацией для одного игрока.',
            };
        }
        if ($locale === 'tj') {
            return match ($slug) {
                'regular' => 'Компютери қулай ва дастрас барои бозии ҳаррӯза.',
                'vip' => 'Экрани равшани QHD, басомади 240 Гц ва таҷҳизоти сатҳи олӣ.',
                'luxe' => 'Компютери пурқувват бо экрани 4K OLED.',
                'streamer-room' => 'Ҳуҷраи омодаи стрим бо таҷҳизоти касбӣ.',
                'bootcamp-dust2' => 'Панҷ компютери якхела барои тамрини даставии CS2 дар DUST2.',
                'bootcamp-mirage' => 'Панҷ компютери якхела барои тамрини даставии CS2 дар MIRAGE.',
                'wingman-01', 'wingman-02' => 'Ҳуҷраи хусусӣ барои бозии дунафарӣ ва ҳамкорӣ.',
                default => 'Ҳуҷраи хусусӣ бо компютери олӣ барои як бозигар.',
            };
        }

        return match ($slug) {
            'regular' => 'A comfortable, affordable setup for everyday gaming.',
            'vip' => 'QHD clarity, 240 Hz, and premium peripherals.',
            'luxe' => 'Flagship solo setup with a 4K OLED display.',
            'streamer-room' => 'A ready-to-stream creator room with broadcast equipment.',
            'bootcamp-dust2' => 'Five matched PCs for CS2 DUST2 team practice.',
            'bootcamp-mirage' => 'Five matched PCs for CS2 MIRAGE team practice.',
            'wingman-01', 'wingman-02' => 'Private two-player room for duo queue and co-op.',
            default => 'Private top-spec room for a premium solo session.',
        };
    }
}
