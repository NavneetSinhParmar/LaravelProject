<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDownload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    private const DOWNLOAD_LIMIT_PER_IP = 3;

    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with('category:id,name')
            ->orderByDesc('id');

        if (! $request->bearerToken()) {
            $query->where('status', true);
        }

        return new JsonResponse([
            'data' => $query->get(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $product = Product::query()
            ->with('category:id,name')
            ->findOrFail($id);

        $payload = $product->toArray();
        $payload['download_count'] = (int) $product->download_count;

        if ($request->bearerToken()) {
            $payload['downloads'] = $product->downloads()
                ->orderByDesc('downloaded_at')
                ->limit(100)
                ->get(['id', 'product_id', 'ip_address', 'downloaded_at']);
        }

        return new JsonResponse([
            'data' => $payload,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, false);
        unset($data['primary_image'], $data['download_file']);

        if ($request->hasFile('primary_image')) {
            $data['primary_image'] = $request->file('primary_image')->store('products/images', 'public');
        }

        if ($request->hasFile('download_file')) {
            $data['download_file'] = $request->file('download_file')->store('products/downloads', 'public');
        }

        $product = Product::query()->create($data);

        return new JsonResponse([
            'message' => 'Product created.',
            'data' => $product->load('category:id,name'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);
        $data = $this->validated($request, true);
        unset($data['primary_image'], $data['download_file']);

        if ($request->hasFile('primary_image')) {
            if ($product->primary_image) {
                Storage::disk('public')->delete($product->primary_image);
            }
            $data['primary_image'] = $request->file('primary_image')->store('products/images', 'public');
        }

        if ($request->hasFile('download_file')) {
            if ($product->download_file) {
                Storage::disk('public')->delete($product->download_file);
            }
            $data['download_file'] = $request->file('download_file')->store('products/downloads', 'public');
        }

        $product->update($data);

        return new JsonResponse([
            'message' => 'Product updated.',
            'data' => $product->fresh()->load('category:id,name'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);

        if ($product->primary_image) {
            Storage::disk('public')->delete($product->primary_image);
        }
        if ($product->download_file) {
            Storage::disk('public')->delete($product->download_file);
        }

        $product->delete();

        return new JsonResponse([
            'message' => 'Product deleted.',
        ]);
    }

    public function download(Request $request, int $id): JsonResponse|StreamedResponse
    {
        $product = Product::query()->findOrFail($id);

        if (! $product->status) {
            return new JsonResponse([
                'message' => 'This product is not available for download.',
            ], 403);
        }

        if (! $product->download_file || ! Storage::disk('public')->exists($product->download_file)) {
            return new JsonResponse([
                'message' => 'Download file not found.',
            ], 404);
        }

        $ip = (string) $request->ip();

        $ipDownloadCount = ProductDownload::query()
            ->where('product_id', $product->id)
            ->where('ip_address', $ip)
            ->count();

        if ($ipDownloadCount >= self::DOWNLOAD_LIMIT_PER_IP) {
            return new JsonResponse([
                'message' => 'Download limit exceeded for this product.',
            ], 403);
        }

        DB::transaction(function () use ($product, $ip): void {
            ProductDownload::query()->create([
                'product_id' => $product->id,
                'ip_address' => $ip,
                'downloaded_at' => now(),
            ]);

            $product->increment('download_count');
        });

        $filename = basename($product->download_file);

        return Storage::disk('public')->download($product->download_file, $filename);
    }

    private function validated(Request $request, bool $update): array
    {
        $data = $request->validate([
            'name' => [$update ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable'],
            'category_id' => ['nullable', 'integer', 'exists:portfolio_categories,id'],
            'seo_tags' => ['nullable', 'string', 'max:2000'],
            'primary_image' => ['nullable', 'file', 'image', 'max:5120'],
            'download_file' => [$update ? 'nullable' : 'required', 'file', 'max:51200'],
        ]);

        if (array_key_exists('status', $data)) {
            $data['status'] = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);
        }

        return $data;
    }
}
