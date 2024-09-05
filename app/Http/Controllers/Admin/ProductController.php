<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Traits\ApiHandlerTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


/**
 * @group products management
 *
 * APIs for managing products.
 */
class ProductController extends Controller implements HasMiddleware
{
    use ApiHandlerTrait;

    /**
     * Define middleware for the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:create_product', only: ['store']),
            new Middleware('permission:update_product', only: ['update']),
            new Middleware('permission:delete_product', only: ['destroy']),
            new Middleware('permission:show_products', only: ['index', 'show']),
        ];
    }

    /**
     * Display a listing of products.
     *
     * @authenticated
     * @response 200 {
     *  "status": true,
     *  "message": "Categories retrieved successfully",
     *  "data": [
     *    {
     *      "id": 1,
     *      "name": "Product Name",
     *      "price": 100.00,
     *      "quantity": 10,
     *      "description": "Product Description",
     *      "category": "Category Name",
     *      "images": [
     *        "http://example.com/storage/images/image1.jpg",
     *        "http://example.com/storage/images/image2.jpg"
     *      ]
     *    }
     *  ]
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     */
    public function index()
    {
        $products = Product::with('images')->get();
        return $this->successResponse("Categories retrieved successfully", ProductResource::collection($products));
    }

    /**
     * Store a newly created product in storage.
     *
     * @authenticated
     * @param  ProductRequest  $productRequest
     * @response 201 {
     *  "status": true,
     *  "message": "Images have been stored successfully",
     *  "data": {
     *    "id": 1,
     *    "name": "Product Name",
     *    "price": 100.00,
     *    "quantity": 10,
     *    "description": "Product Description",
     *    "category": "Category Name",
     *    "images": [
     *      "http://example.com/storage/images/image1.jpg",
     *      "http://example.com/storage/images/image2.jpg"
     *    ]
     *  }
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     * @response 422 {
     *  "status": false,
     *  "message": "The given data was invalid.",
     *  "data": {
     *    "errors": {
     *      "images": ["The images field is required."]
     *    }
     *  }
     * }
     */
    public function store(ProductRequest $productRequest)
    {
        $product = null;
        DB::transaction(function () use ($productRequest, &$product) {
            $category = Category::findOrFail($productRequest->category_id);

            // Use the relationship method to create a new product
            $product = $category->products()->create([
                'name' => $productRequest->name,
                'price' => $productRequest->price,
                'quantity' => $productRequest->quantity,
                'description' => $productRequest->description,
            ]);

            // Handle image uploads
            foreach ($productRequest->images as $image) {
                $imageName = storeImage($image);
                $product->images()->create([
                    'name' => $imageName,
                ]);
            }
        });

        return $this->successResponse('Images have been stored successfully', new ProductResource($product));
    }

    /**
     * Display the specified product.
     *
     * @authenticated
     * @param  int  $id
     * @response 200 {
     *  "status": true,
     *  "message": "Product retrieved successfully",
     *  "data": {
     *    "id": 1,
     *    "name": "Product Name",
     *    "price": 100.00,
     *    "quantity": 10,
     *    "description": "Product Description",
     *    "category": "Category Name",
     *    "images": [
     *      "http://example.com/storage/images/image1.jpg",
     *      "http://example.com/storage/images/image2.jpg"
     *    ]
     *  }
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
             * @response 404 {
     *  "status": false,
     *  "message": "No resource was found",
     *  "data": null
     * }
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return $this->successResponse('Product retrieved successfully', new ProductResource($product));
    }

    /**
     * Update the specified product in storage.
     *
     * @authenticated
     * @param  ProductRequest  $productRequest
     * @param  int  $id
     * @response 200 {
     *  "status": true,
     *  "message": "Product updated successfully",
     *  "data": {
     *    "id": 1,
     *    "name": "Updated Product Name",
     *    "price": 150.00,
     *    "quantity": 20,
     *    "description": "Updated Product Description",
     *    "category": "Category Name",
     *    "images": [
     *      "http://example.com/storage/images/image1.jpg",
     *      "http://example.com/storage/images/image2.jpg"
     *    ]
     *  }
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
           * @response 404 {
     *  "status": false,
     *  "message": "No resource was found",
     *  "data": null
     * }
     * @response 422 {
     *  "status": false,
     *  "message": "The given data was invalid.",
     *  "data": {
     *    "errors": {
     *      "images": ["The images must be an array of valid image files."]
     *    }
     *  }
     * }
     */
    public function update(ProductRequest $productRequest, $id)
    {
        $product = Product::findOrFail($id);
        $product->name = $productRequest->name;
        $product->price = $productRequest->price;
        $product->quantity = $productRequest->quantity;
        $product->description = $productRequest->description;
        $product->category_id = $productRequest->category_id;

        if ($productRequest->hasFile('images')) {
            foreach ($product->images as $image) {
                $imagePath = 'images/' . $image->name;
                Storage::disk('public')->delete($imagePath);
                $image->delete();
            }
            foreach ($productRequest->images as $image) {
                $imageName = storeImage($image);
                $product->images()->create([
                    'name' => $imageName,
                ]);
            }
        }
        $product->update();
        return $this->successResponse('Product updated successfully', new ProductResource($product));
    }

    /**
     * Remove the specified product from storage.
     *
     * @authenticated
     * @param  int  $id
     * @response 200 {
     *  "status": true,
     *  "message": "Product deleted successfully",
     *  "data": null
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
        * @response 404 {
     *  "status": false,
     *  "message": "No resource was found",
     *  "data": null
     * }
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        foreach ($product->images as $image) {
            $imagePath = 'images/' . $image->name;
            Storage::disk('public')->delete($imagePath);
        }

        $product->delete();

        return $this->successResponse('Product deleted successfully');
    }
}
