<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Schema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class RecommendProducts implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return <<<TEXT
Recommend products from the Jamal Mart catalog.

Use this tool whenever the customer asks:

- Recommend a product
- Best product
- Best phone
- Best laptop
- Best shoes
- Which one should I buy?
- What do you recommend?

Supports filtering by:

- category
- brand
- minimum price
- maximum price
- in stock

Returns the most suitable products based on the available catalog.

Never recommend products that are not returned by this tool.
TEXT;
    }
    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = Product::query()
            ->with([
                'brand',
                'category',
            ])
            ->where('is_active', true);


        if ($request->filled('query')) {
            $keywords = preg_split('/\s+/', trim($request['query']));
            $query->where(function ($outer) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $outer->where(function ($inner) use ($keyword) {
                        $inner
                            ->orWhere('name', 'LIKE', "%{$keyword}%")
                            ->orWhere('description', 'LIKE', "%{$keyword}%")
                            ->orWhereHas('brand', function ($brand) use ($keyword) {

                                $brand->where(
                                    'name',
                                    'LIKE',
                                    "%{$keyword}%"
                                );
                            })
                            ->orWhereHas('category', function ($category) use ($keyword) {
                                $category->where(
                                    'name',
                                    'LIKE',
                                    "%{$keyword}%"
                                );
                            });
                    });
                }
            });
        }

        //  Brand Filter
        if ($request->filled('brand')) {
            $brand = trim($request['brand']);
            $query->whereHas('brand', function ($q) use ($brand) {
                $q->where(
                    'name',
                    'LIKE',
                    "%{$brand}%"
                );
            });
        }

        // Category Filter
        if ($request->filled('category')) {
            $category = trim($request['category']);
            $query->whereHas('category', function ($q) use ($category) {
                $q->where(
                    'name',
                    'LIKE',
                    "%{$category}%"
                );
            });
        }

        // Minimum Price
        if ($request->filled('min_price')) {
            $query->where(
                'price',
                '>=',
                (float) $request['min_price']
            );
        }

        //   Maximum Price
        if ($request->filled('max_price')) {
            $query->where(
                'price',
                '<=',
                (float) $request['max_price']
            );
        }

        //  Stock Filter
        if ($request->filled('in_stock') && $request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }


        $query->orderByDesc('created_at')->orderBy('price');

        // Results
        $products = $query
            ->limit(5)
            ->get();

        //  No Products
        if ($products->isEmpty()) {
            return json_encode([
                'success' => false,
                'count' => 0,
                'recommended_products' => [],
                'message' => 'No recommended products found.'
            ], JSON_PRETTY_PRINT);
        }

        //  Response
        $response = [
            'success' => true,
            'count' => $products->count(),
            'recommended_products' => $products->map(function ($product) {
                return [
                    'name' => $product->name,
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                    'price' => (float) $product->price,
                    'description' => $product->description,
                    'image' => $product->image,
                    'in_stock' => (bool) ($product->stock > 0),
                ];
            })->values(),

        ];

        return json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [

            'query' => $schema
                ->string()
                ->description('Optional search keyword.'),

            'brand' => $schema
                ->string()
                ->description('Optional brand filter.'),

            'category' => $schema
                ->string()
                ->description('Optional category filter.'),

            'min_price' => $schema
                ->number()
                ->description('Minimum price.'),

            'max_price' => $schema
                ->number()
                ->description('Maximum price.'),

            'in_stock' => $schema
                ->boolean()
                ->description('Return only products currently in stock.'),

        ];
    }
}
