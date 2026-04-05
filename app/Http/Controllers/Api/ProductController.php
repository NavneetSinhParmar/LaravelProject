<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'data' => Product::query()->latest('id')->get(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return new JsonResponse([
            'data' => Product::query()->findOrFail($id),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $product = Product::query()->create(
            $request->validate($this->rules())
        );

        return new JsonResponse([
            'message' => 'Product created.',
            'data' => $product,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);
        $product->update($request->validate($this->rules()));

        return new JsonResponse([
            'message' => 'Product updated.',
            'data' => $product->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        Product::query()->findOrFail($id)->delete();

        return new JsonResponse([
            'message' => 'Product deleted.',
        ]);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'json_data' => ['nullable', 'array'],
            'status' => ['required', 'boolean'],
        ];
    }
}
