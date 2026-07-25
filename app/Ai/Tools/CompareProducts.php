<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CompareProducts implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return <<<TEXT
Compare two or more products from the Jamal Mart catalog.

Use this tool whenever the customer asks:

- Compare products
- Which one is better?
- What's the difference?
- Compare the first and second product
- Compare Product A with Product B

Returns a side-by-side comparison of matching products.

Never compare products that are not found in the catalog.
TEXT;
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {

        // Validate Input
        $productNames = collect($request['products'] ?? [])->filter()->map(fn($name) => trim($name))->unique()->values();

        if ($productNames->count() < 2) {
            return json_encode([
                'success' => false,
                'message' => 'Please provide at least two products to compare.',
            ], JSON_PRETTY_PRINT);
        }

        // Query Products
        $query = Product::query()->with(['brand', 'category',])->where('is_active', true);

        $query->where(function ($q) use ($productNames) {
            foreach ($productNames as $name) {
                $q->orWhere('name', 'LIKE', "%{$name}%");
            }
        });

        $products = $query->get();

        // Products Not Found
        if ($products->count() < 2) {
            return json_encode([
                'success' => false,
                'message' => 'Unable to find enough matching products to compare.',
            ], JSON_PRETTY_PRINT);
        }

        //  Prepare Comparison Data
        $comparison = $products->map(function ($product) {
            return [
                'name' => $product->name,
                'brand' => $product->brand?->name,
                'category' => $product->category?->name,
                'price' => (float) $product->price,
                'description' => $product->description,
                'image' => $product->image,
                'in_stock' => $product->stock > 0,
                'stock_quantity' => (int) $product->stock,
            ];
        })->values();
        //  Calculate Summary
        $cheapest = $products->sortBy('price')->first();
        $mostExpensive = $products->sortByDesc('price')->first();
        $inStock = $products->where('stock', '>', 0)->pluck('name')->values();

        // Response
        $response = [
            'success' => true,
            'count' => $comparison->count(),
            'comparison' => [
                'products' => $comparison,
                'summary' => [
                    'cheapest_product' => $cheapest?->name,
                    'highest_priced_product' => $mostExpensive?->name,
                    'price_range' => [
                        'lowest' => (float) $cheapest?->price,
                        'highest' => (float) $mostExpensive?->price,
                    ],
                    'products_in_stock' => $inStock,
                ],

            ],

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
            'products' => $schema->array($schema->string())->description('List of product names to compare.'),
        ];
    }
}
