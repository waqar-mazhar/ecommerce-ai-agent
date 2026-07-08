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
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search product from the store by name and brand, category, price,stock, and description';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        Log::info($request->all());
        $query = Product::query()->with('category', 'brand')->where('is_active', true);;

        if ($request['query']) {

            $search = $request['query'];


            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")

                    ->orWhere('description', 'like', "%{$search}%")

                    ->orWhereHas('category', function ($category) use ($search) {

                        $category->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    })

                    ->orWhereHas('brand', function ($brand) use ($search) {

                        $brand->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    });
            });
        }

        if ($request['brand'] ?? null) {

            $query->whereHas('brand', function ($q) use ($request) {

                $q->where(
                    'name',
                    'like',
                    "%{$request['brand']}%"
                );
            });
        }



        if ($request['category'] ?? null) {

            $query->whereHas('category', function ($q) use ($request) {

                $q->where(
                    'name',
                    'like',
                    "%{$request['category']}%"
                );
            });
        }



        if ($request['max_price'] ?? null) {

            $query->where(
                'price',
                '<=',
                $request['max_price']
            );
        }


        $products = $query->limit(10)->get();



        if ($products->isEmpty()) {
            return "No products found.";
        }

        // dd($products->toArray());
        Log::info($products->toJson());

        return $products->toJson(JSON_PRETTY_PRINT);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema
                ->string()
                ->description('Product keyword such as shoes, nike shoes, iphone, keyboard')->required(),

            'brand' => $schema
                ->string()
                ->description('Brand name'),

            'category' => $schema
                ->string()
                ->description('Category Name'),

            'max_price' => $schema->integer()->description('maximum price')
        ];
    }
}
