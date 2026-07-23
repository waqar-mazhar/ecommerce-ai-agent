<?php

namespace App\Ai\Tools;

use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchCategories implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return <<<TEXT
Search available product categories in the Jamal Mart catalog.

Use this tool whenever the customer asks about:

- Available categories
- Product categories
- Whether a category exists
- Categories for a specific brand

Supports filtering by:

- category
- brand

Returns matching categories along with the number of active products in each category.
TEXT;
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = Category::query()
            ->whereHas('products', function ($q) {
                $q->where('is_active', true);
            })
            ->withCount([
                'products as product_count' => function ($q) use ($request) {
                    $q->where('is_active', true);
                    if ($request->filled('brand')) {
                        $brand = trim($request['brand']);
                        $q->whereHas('brand', function ($brandQuery) use ($brand) {
                            $brandQuery->where(
                                'name',
                                'LIKE',
                                "%{$brand}%"
                            );
                        });
                    }
                }
            ]);

        /*
        |--------------------------------------------------------------------------
        | Category Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category')) {
            $category = trim($request['category']);
            $query->where(
                'name',
                'LIKE',
                "%{$category}%"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Brand Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('brand')) {
            $brand = trim($request['brand']);
            $query->whereHas('products.brand', function ($q) use ($brand) {
                $q->where(
                    'name',
                    'LIKE',
                    "%{$brand}%"
                );
            });
        }

        $categories = $query->get();

        if ($categories->isEmpty()) {
            return json_encode([
                'count' => 0,
                'categories' => [],
                'message' => 'No matching categories found.'
            ], JSON_PRETTY_PRINT);
        }

        $response = [
            'count' => $categories->count(),
            'categories' => $categories->map(function ($category) {
                return [
                    'name' => $category->name,
                    'products' => $category->product_count,
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

            'category' => $schema
                ->string()
                ->description(
                    'Optional category name to search. Example: Mobiles, Laptops, Shoes.'
                ),

            'brand' => $schema
                ->string()
                ->description(
                    'Optional brand filter. Example: Samsung, Apple, Dell.'
                ),

        ];
    }
}
