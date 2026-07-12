<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchProducts implements Tool
{
    /**
     * Tool description.
     */
    public function description(): Stringable|string
    {
        return <<<TEXT
Search products from the Jamal Mart catalog.

Use this tool whenever a customer asks about:

- Products
- Product names
- Brands
- Categories
- Prices
- Recommendations
- Product comparisons
- Shopping

Supports searching by:

- query
- brand
- category
- maximum price

Returns up to 10 matching active products including:

- id
- name
- brand
- category
- price
- description
- image
- stock status

Never answer product-related questions without using this tool first.
TEXT;
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        Log::info('SearchProducts Request', $request->all());

        $query = Product::query()
            ->with(['brand', 'category'])
            ->where('is_active', true);

        /*
        |--------------------------------------------------------------------------
        | Keyword Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('query')) {
            $keywords = preg_split('/\s+/', trim($request['query']));
            $query->where(function ($outer) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $outer->where(function ($inner) use ($keyword) {
                        $inner
                            ->orWhere('name', 'LIKE', "%{$keyword}%")
                            ->orWhere('description', 'LIKE', "%{$keyword}%")
                            ->orWhereHas('brand', function ($brand) use ($keyword) {
                                $brand->where('name', 'LIKE', "%{$keyword}%");
                            })
                            ->orWhereHas('category', function ($category) use ($keyword) {
                                $category->where('name', 'LIKE', "%{$keyword}%");
                            });
                    });
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Brand Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('brand')) {

            $query->whereHas('brand', function ($brand) use ($request) {

                $brand->where(
                    'name',
                    'LIKE',
                    '%' . trim($request['brand']) . '%'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Category Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category')) {

            $query->whereHas('category', function ($category) use ($request) {

                $category->where(
                    'name',
                    'LIKE',
                    '%' . trim($request['category']) . '%'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Price
        |--------------------------------------------------------------------------
        */

        if ($request->filled('max_price')) {

            $query->where(
                'price',
                '<=',
                (float) $request['max_price']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Results
        |--------------------------------------------------------------------------
        */

        $products = $query
            ->orderBy('name')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | No Products Found
        |--------------------------------------------------------------------------
        */

        if ($products->isEmpty()) {

            return json_encode([
                'products' => [],
                'count' => 0,
                'message' => 'No matching products found.'
            ], JSON_PRETTY_PRINT);
        }

        /*
        |--------------------------------------------------------------------------
        | AI Friendly Response
        |--------------------------------------------------------------------------
        */

        $response = [
            'count' => $products->count(),
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                    'price' => $product->price,
                    'description' => $product->description,
                    'image' => $product->image,
                    'in_stock' => (bool) ($product->stock > 0),
                ];
            })->values(),

        ];

        Log::info('SearchProducts Response', $response);

        return json_encode($response, JSON_PRETTY_PRINT);
    }

    /**
     * Tool schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [

            'query' => $schema
                ->string()
                ->description(
                    'General search keyword such as "running shoes", "iphone", "wireless keyboard", or "milk".'
                ),

            'brand' => $schema
                ->string()
                ->description(
                    'Brand name such as Nike, Samsung, Apple, Dell, HP, Nestle.'
                ),

            'category' => $schema
                ->string()
                ->description(
                    'Category such as Shoes, Mobiles, Laptops, Grocery, Accessories.'
                ),

            'max_price' => $schema
                ->integer()
                ->description('Maximum product price.'),

        ];
    }
}
