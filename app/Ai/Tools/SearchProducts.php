<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
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
- Comparisons
- Shopping
- Available products

Supports the following filters:

- query
- brand
- category
- minimum price
- maximum price
- in stock
- sorting
- pagination

Available sorting options:

- name
- newest
- price_low
- price_high

Returns paginated active products including:

- Product name
- Brand
- Category
- Price
- Description
- Image URL
- Stock status

Never answer product-related questions without calling this tool first.

Always extract as many parameters as possible from the user's request.
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

            $keyword = trim($request['query']);

            $query->where(function ($q) use ($keyword) {

                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%")
                    ->orWhereHas('brand', function ($brand) use ($keyword) {
                        $brand->where('name', 'LIKE', "%{$keyword}%");
                    })
                    ->orWhereHas('category', function ($category) use ($keyword) {
                        $category->where('name', 'LIKE', "%{$keyword}%");
                    });
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Brand Filter
    |--------------------------------------------------------------------------
    */

        if ($request->filled('brand')) {

            $brand = trim($request['brand']);

            $query->whereHas('brand', function ($q) use ($brand) {
                $q->where('name', 'LIKE', "%{$brand}%");
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Category Filter
    |--------------------------------------------------------------------------
    */

        if ($request->filled('category')) {

            $category = trim($request['category']);

            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', 'LIKE', "%{$category}%");
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Minimum Price
    |--------------------------------------------------------------------------
    */

        if ($request->filled('min_price')) {

            $query->where(
                'price',
                '>=',
                (float) $request['min_price']
            );
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
    | Stock Filter
    |--------------------------------------------------------------------------
    */

        if ($request->filled('in_stock') && $request['in_stock']) {

            $query->where('stock', '>', 0);
        }

        /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    */

        switch ($request['sort'] ?? 'name') {

            case 'price_low':
                $query->orderBy('price');
                break;

            case 'price_high':
                $query->orderByDesc('price');
                break;

            case 'newest':
                $query->latest();
                break;

            case 'name':
            default:
                $query->orderBy('name');
                break;
        }

        /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

        $page = max(1, (int) ($request['page'] ?? 1));

        $limit = min(
            20,
            max(
                1,
                (int) ($request['limit'] ?? 10)
            )
        );

        $products = $query->paginate(
            perPage: $limit,
            page: $page
        );

        /*
    |--------------------------------------------------------------------------
    | No Results
    |--------------------------------------------------------------------------
    */

        if ($products->isEmpty()) {

            return json_encode([
                'count' => 0,
                'total' => 0,
                'page' => 1,
                'last_page' => 1,
                'has_more' => false,
                'products' => [],
                'message' => 'No matching products found.'
            ], JSON_PRETTY_PRINT);
        }

        /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

        $response = [

            'count' => $products->count(),

            'total' => $products->total(),

            'page' => $products->currentPage(),

            'last_page' => $products->lastPage(),

            'has_more' => $products->hasMorePages(),

            'products' => $products->map(function ($product) {

                return [

                    'name' => $product->name,

                    'brand' => $product->brand?->name,

                    'category' => $product->category?->name,

                    'price' => [
                        'amount' => (float) $product->price,
                        'currency' => 'PKR',
                    ],

                    'description' => $product->description,

                    'image' => $product->image
                        ? $product->image
                        : null,

                    'in_stock' => $product->stock > 0,

                ];
            })->values(),

        ];

        Log::info('SearchProducts Response', $response);

        return json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
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
                    'General search keywords such as "wireless keyboard", "gaming laptop", "running shoes", or "iphone".'
                ),

            'brand' => $schema
                ->string()
                ->description(
                    'Brand name such as Apple, Samsung, Dell, HP, Lenovo, Nike or Nestle.'
                ),

            'category' => $schema
                ->string()
                ->description(
                    'Product category such as Mobiles, Laptops, Shoes, Grocery, Accessories or Electronics.'
                ),

            'min_price' => $schema
                ->number()
                ->description(
                    'Minimum product price. Example: 1000'
                ),

            'max_price' => $schema
                ->number()
                ->description(
                    'Maximum product price. Example: 50000'
                ),

            'in_stock' => $schema
                ->boolean()
                ->description(
                    'Return only products currently in stock.'
                ),

            'sort' => $schema
                ->string()
                ->enum([
                    'name',
                    'newest',
                    'price_low',
                    'price_high',
                ])
                ->description(
                    'Sort order for returned products.'
                ),

            'page' => $schema
                ->integer()
                ->description(
                    'Page number for paginated results.'
                ),

            'limit' => $schema
                ->integer()
                ->description(
                    'Maximum number of products to return. Maximum allowed is 20.'
                ),

        ];
    }
}
