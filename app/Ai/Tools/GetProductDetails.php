<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetProductDetails implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return <<<TEXT
Get complete details for a single product from the Jamal Mart catalog.

Use this tool whenever the customer asks about:

- Product details
- Tell me more
- Show product information
- Product description
- Product image
- Is it in stock?
- Show that product again
- Specifications of a product

This tool returns complete information for one product.

Use this tool after SearchProducts whenever the customer wants more information about a specific product.
TEXT;
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {

        $query = Product::query()->with(['brand', 'category',])->where('is_active', true);

        // Search by Product Name
        if ($request->filled('product')) {
            $product = trim($request['product']);
            $query->where('name', 'LIKE', "%{$product}%");
        }

        $product = $query->first();

        // Product Not Found
        if (!$product) {
            return json_encode([
                'found' => false,
                'message' => 'Product not found.'
            ], JSON_PRETTY_PRINT);
        }

        // Response
        $response = [
            'found' => true,
            'product' => [
                'name' => $product->name,
                'brand' => $product->brand?->name,
                'category' => $product->category?->name,
                'price' => [
                    'amount' => (float) $product->price,
                    'currency' => 'PKR',
                ],
                'description' => $product->description,
                'image' => $product->image,
                'in_stock' => $product->stock > 0,
                'stock_quantity' => (int) $product->stock,
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
            'product' => $schema->string()->description('Product name. Example: iPhone 16 Pro, Galaxy S25, Nike Air Max.'),
        ];
    }
}
