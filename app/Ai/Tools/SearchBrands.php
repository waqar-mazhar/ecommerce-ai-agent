<?php

namespace App\Ai\Tools;

use App\Models\Brand;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchBrands implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return <<<TEXT
Search available brands in the Jamal Mart catalog.

Use this tool whenever the customer asks about:

- Available brands
- Brand names
- Brands within a category
- Whether a brand exists

Supports filtering by:

- brand
- category

Returns matching brands along with the number of active products for each brand.
TEXT;
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        Log::info('SearchBrands Request', $request->all());

        $query = Brand::query()
            ->whereHas('products', function ($q) {
                $q->where('is_active', true);
            })

            ->withCount([
                'products as product_count' => function ($q) use ($request) {
                    $q->where('is_active', true);
                    if ($request->filled('category')) {
                        $category = trim($request['category']);
                        $q->whereHas('category', function ($categoryQuery) use ($category) {

                            $categoryQuery->where(
                                'name',
                                'LIKE',
                                "%{$category}%"
                            );
                        });
                    }
                }
            ]);

        /*
        |--------------------------------------------------------------------------
        | Brand Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('brand')) {
            $brand = trim($request['brand']);
            $query->where(
                'name',
                'LIKE',
                "%{$brand}%"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Category Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category')) {
            $category = trim($request['category']);
            $query->whereHas('products.category', function ($q) use ($category) {
                $q->where(
                    'name',
                    'LIKE',
                    "%{$category}%"
                );
            });
        }

        $brands = $query->orderBy('name')->get();

        if ($brands->isEmpty()) {
            return json_encode([
                'count' => 0,
                'brands' => [],
                'message' => 'No matching brands found.'
            ], JSON_PRETTY_PRINT);
        }

        $response = [
            'count' => $brands->count(),
            'brands' => $brands->map(function ($brand) {
                return [
                    'name' => $brand->name,
                    'products' => $brand->product_count,
                ];
            })->values(),

        ];

        Log::info('SearchBrands Response', $response);

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

            'brand' => $schema
                ->string()
                ->description(
                    'Optional brand name to search. Example: Samsung, Apple, Nike.'
                ),

            'category' => $schema
                ->string()
                ->description(
                    'Optional category filter. Example: Mobiles, Laptops, Shoes.'
                ),

        ];
    }
}
