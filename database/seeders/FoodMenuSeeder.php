<?php

namespace Database\Seeders;

use App\Models\FoodItem;
use Illuminate\Database\Seeder;

class FoodMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menu = [
            ['classic-burger', 'Classic Burger', 'Burgers', 'Beef patty, cheese, lettuce, tomato, and house sauce.', 5500, 'photo-1568901346375-23c9450c58cd'],
            ['double-smash', 'Double Smash Burger', 'Burgers', 'Two seared patties, cheddar, pickles, and house sauce.', 7500, 'photo-1550547660-d9450f859349'],
            ['chicken-wrap', 'Grilled Chicken Wrap', 'Mains', 'Grilled chicken, crisp greens, and garlic yogurt sauce.', 4900, 'photo-1626700051175-6818013e1d4f'],
            ['club-pizza', 'Club Pizza', 'Mains', 'Tomato, mozzarella, and a choice of pepperoni or vegetables.', 6900, 'photo-1513104890138-7c749659a591'],
            ['loaded-fries', 'Loaded Fries', 'Snacks', 'Golden fries with cheese sauce and spring onion.', 2500, 'photo-1573080496219-bb080dd4f877'],
            ['crispy-wings', 'Crispy Chicken Wings', 'Snacks', 'Eight wings tossed in your choice of mild or spicy sauce.', 4500, 'photo-1562967914-608f82629710'],
            ['caesar-salad', 'Chicken Caesar Salad', 'Fresh', 'Chicken, romaine, parmesan, croutons, and Caesar dressing.', 4800, 'photo-1540189549336-e6e99c3679fe'],
            ['cola', 'Cola', 'Drinks', 'Chilled 500 ml bottle.', 1200, 'photo-1554866585-cd94860890b7'],
            ['water', 'Still Water', 'Drinks', 'Chilled 500 ml bottle.', 500, 'photo-1548839140-29a749e1cf4d'],
            ['energy-drink', 'Energy Drink', 'Drinks', 'Chilled 250 ml can.', 1800, 'photo-1622543925917-763c34d1a86e'],
        ];

        foreach ($menu as $sort => [$slug, $name, $category, $description, $priceCents, $photo]) {
            FoodItem::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'category' => $category,
                'description' => $description,
                'price_cents' => $priceCents,
                'currency' => 'TJS',
                'image_url' => 'https://images.unsplash.com/'.$photo.'?auto=format&fit=crop&w=900&q=80',
                'is_available' => true,
                'sort_order' => $sort + 1,
            ]);
        }
    }
}
