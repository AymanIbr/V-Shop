<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::updateOrCreate(
            ['slug' => 'galaxy-s25'],
            [
                'title'        => 'Galaxy S25',
                'description'  => 'Samsung flagship phone.',
                'price'        => 899.99,
                'quantity'     => 20,
                'in_stock'     => true,
                'is_published' => true,
                'brand_id'     => Brand::where('slug', 'samsung')->value('id'),
                'category_id'  => Category::where('slug', 'electronics')->value('id'),
            ]
        );
    }
}
