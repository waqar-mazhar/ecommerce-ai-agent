<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categories = [
            'Electronics',
            'Mobiles',
            'Laptops',
            'Clothing',
            'Shoes',
            'Home Appliances',
            'Beauty',
            'Sports',
        ];


        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'slug' => Str::slug($category),
                'description' => "Products related to {$category}",
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Brands
        |--------------------------------------------------------------------------
        */

        $brands = [
            'Apple',
            'Samsung',
            'Nike',
            'Adidas',
            'Sony',
            'Dell',
            'HP',
            'Lenovo',
            'LG',
            'Nestle',
        ];


        foreach ($brands as $brand) {

            Brand::create([
                'name' => $brand,
                'slug' => Str::slug($brand),
                'description' => "{$brand} products",
            ]);
        }



        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */


        $products = [

            [
                'name' => 'iPhone 16 Pro',
                'category' => 'Mobiles',
                'brand' => 'Apple',
                'price' => 1199,
                'stock' => 30,
                'description' => 'Latest Apple smartphone with advanced camera system'
            ],

            [
                'name' => 'Samsung Galaxy S25',
                'category' => 'Mobiles',
                'brand' => 'Samsung',
                'price' => 999,
                'stock' => 45,
                'description' => 'Premium Samsung flagship smartphone'
            ],

            [
                'name' => 'MacBook Pro M4',
                'category' => 'Laptops',
                'brand' => 'Apple',
                'price' => 2200,
                'stock' => 20,
                'description' => 'Professional laptop powered by Apple M4 chip'
            ],

            [
                'name' => 'Dell XPS 15',
                'category' => 'Laptops',
                'brand' => 'Dell',
                'price' => 1800,
                'stock' => 15,
                'description' => 'High performance Dell laptop'
            ],

            [
                'name' => 'HP Pavilion Laptop',
                'category' => 'Laptops',
                'brand' => 'HP',
                'price' => 850,
                'stock' => 25,
                'description' => 'Affordable everyday laptop'
            ],

            [
                'name' => 'Lenovo ThinkPad X1',
                'category' => 'Laptops',
                'brand' => 'Lenovo',
                'price' => 1400,
                'stock' => 18,
                'description' => 'Business class Lenovo laptop'
            ],

            [
                'name' => 'Nike Air Max Shoes',
                'category' => 'Shoes',
                'brand' => 'Nike',
                'price' => 180,
                'stock' => 100,
                'description' => 'Comfortable running shoes'
            ],

            [
                'name' => 'Adidas Ultraboost',
                'category' => 'Shoes',
                'brand' => 'Adidas',
                'price' => 160,
                'stock' => 80,
                'description' => 'Premium sports running shoes'
            ],

            [
                'name' => 'Sony Wireless Headphones',
                'category' => 'Electronics',
                'brand' => 'Sony',
                'price' => 300,
                'stock' => 60,
                'description' => 'Noise cancelling wireless headphones'
            ],

            [
                'name' => 'Samsung Smart TV 55 inch',
                'category' => 'Electronics',
                'brand' => 'Samsung',
                'price' => 900,
                'stock' => 25,
                'description' => '4K smart television'
            ],

            [
                'name' => 'LG Washing Machine',
                'category' => 'Home Appliances',
                'brand' => 'LG',
                'price' => 700,
                'stock' => 12,
                'description' => 'Automatic washing machine'
            ],

            [
                'name' => 'Nestle Coffee Pack',
                'category' => 'Beauty',
                'brand' => 'Nestle',
                'price' => 15,
                'stock' => 200,
                'description' => 'Premium instant coffee'
            ],

            [
                'name' => 'Apple AirPods Pro',
                'category' => 'Electronics',
                'brand' => 'Apple',
                'price' => 250,
                'stock' => 70,
                'description' => 'Wireless earbuds with noise cancellation'
            ],

            [
                'name' => 'Samsung Galaxy Watch',
                'category' => 'Electronics',
                'brand' => 'Samsung',
                'price' => 350,
                'stock' => 40,
                'description' => 'Smart watch with health tracking'
            ],

            [
                'name' => 'Nike Sports T-Shirt',
                'category' => 'Clothing',
                'brand' => 'Nike',
                'price' => 50,
                'stock' => 150,
                'description' => 'Comfortable sports clothing'
            ],

            [
                'name' => 'Adidas Hoodie',
                'category' => 'Clothing',
                'brand' => 'Adidas',
                'price' => 90,
                'stock' => 90,
                'description' => 'Warm casual hoodie'
            ],

            [
                'name' => 'Sony Bluetooth Speaker',
                'category' => 'Electronics',
                'brand' => 'Sony',
                'price' => 120,
                'stock' => 55,
                'description' => 'Portable wireless speaker'
            ],

            [
                'name' => 'HP Wireless Mouse',
                'category' => 'Electronics',
                'brand' => 'HP',
                'price' => 25,
                'stock' => 300,
                'description' => 'Ergonomic wireless mouse'
            ],

            [
                'name' => 'Dell Monitor 27 inch',
                'category' => 'Electronics',
                'brand' => 'Dell',
                'price' => 350,
                'stock' => 35,
                'description' => 'Full HD computer monitor'
            ],

            [
                'name' => 'Lenovo Wireless Keyboard',
                'category' => 'Electronics',
                'brand' => 'Lenovo',
                'price' => 40,
                'stock' => 120,
                'description' => 'Wireless keyboard for computers'
            ],


        ];



        foreach ($products as $item) {

            $category = Category::where('name', $item['category'])
                ->first();


            $brand = Brand::where('name', $item['brand'])
                ->first();



            Product::create([

                'category_id' => $category->id,

                'brand_id' => $brand->id,

                'name' => $item['name'],

                'slug' => Str::slug($item['name']),

                'description' => $item['description'],

                'price' => $item['price'],

                'stock' => $item['stock'],

                'sku' => strtoupper(Str::random(10)),

                'is_active' => true,

            ]);
        }
    }
}
