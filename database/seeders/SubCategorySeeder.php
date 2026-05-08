<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->command->info('Please seed the categories first.');
            return;
        }

        $subCategories = [
            ['name' => 'Mobiles', 'category_id' => $categories->where('name', 'Electronics')->first()->id],
            ['name' => 'Laptops', 'category_id' => $categories->where('name', 'Electronics')->first()->id],
            ['name' => 'Fiction', 'category_id' => $categories->where('name', 'Books')->first()->id],
            ['name' => 'Non-Fiction', 'category_id' => $categories->where('name', 'Books')->first()->id],
            ['name' => 'Men', 'category_id' => $categories->where('name', 'Clothing')->first()->id],
            ['name' => 'Women', 'category_id' => $categories->where('name', 'Clothing')->first()->id],
            ['name' => 'Kitchen Appliances', 'category_id' => $categories->where('name', 'Home & Kitchen')->first()->id],
            ['name' => 'Home Decor', 'category_id' => $categories->where('name', 'Home & Kitchen')->first()->id],
            ['name' => 'Cricket', 'category_id' => $categories->where('name', 'Sports & Outdoors')->first()->id],
            ['name' => 'Football', 'category_id' => $categories->where('name', 'Sports & Outdoors')->first()->id],
        ];

        foreach ($subCategories as $subCategory) {
            SubCategory::firstOrCreate($subCategory);
        }
    }
}
