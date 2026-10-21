<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DemoMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            'Burger' => [
                ['Classic Mak Yay', 25000, 'Roti lembut, patty sapi, selada segar, tomat, dan saus khas Mak Yay.'],
                ['Cheese Please', 32000, 'Patty sapi dengan keju cheddar leleh, acar, dan saus creamy.'],
                ['Double Trouble', 45000, 'Dua patty sapi dan dua lapis keju untuk lapar yang serius.'],
                ['Crispy Chicken', 28000, 'Ayam renyah, coleslaw, dan mayo dalam roti panggang lembut.'],
            ],
            'Kebab' => [
                ['Kebab Beef Mak Yay', 25000, 'Daging sapi panggang gurih, selada, dan saus spesial dalam kulit tortilla renyah.'],
                ['Kebab Chicken Crispy', 22000, 'Ayam krispi renyah dengan mayo gurih dan sayuran segar.'],
                ['Kebab Keju Melted', 28000, 'Daging sapi lezat dengan lelehan keju lumer yang berlimpah.'],
                ['Kebab Spesial Jumbo', 35000, 'Porsi besar daging sapi ganda dan keju lumer melimpah.'],
            ],
        ];

        foreach ($menus as $categoryName => $products) {
            $category = Category::firstOrCreate(['name' => $categoryName]);
            foreach ($products as [$name, $price, $description]) {
                Product::withTrashed()->firstOrCreate(
                    ['name' => $name, 'category_id' => $category->id],
                    ['price' => $price, 'description' => $description, 'stock' => 30, 'is_active' => true]
                );
            }
        }

        $addons = [
            ['Extra Cheese Slice', 5000],
            ['Extra Beef Patty', 12000],
            ['Telur Mata Sapi', 4000],
            ['Saus Barbeque', 3000],
            ['Saus Keju Melted', 4000],
            ['Saus Sambal Pedas Mak Yay', 2000],
        ];

        foreach ($addons as [$name, $price]) {
            Addon::firstOrCreate(
                ['name' => $name],
                ['price' => $price, 'is_active' => true]
            );
        }

        $this->command?->warn('Menu, harga, dan add-on adalah DATA CONTOH; sesuaikan sebelum berjualan.');
    }
}
