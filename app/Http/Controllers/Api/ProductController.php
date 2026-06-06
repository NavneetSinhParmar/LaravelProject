<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDownload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    private const DOWNLOAD_LIMIT_PER_EMAIL_IP = 3;
    private const DOWNLOAD_ROUTE_EXPIRY_MINUTES = 10;

    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with('category:id,name')
            ->orderByDesc('id');

        if (! $request->bearerToken()) {
            $query->where('status', true);
        }

        $hasShortDescription = Schema::hasColumn('products', 'short_description');
        $hasProductType = Schema::hasColumn('products', 'product_type');

        if ($search = $request->string('search')->trim()) {
            $query->where(function ($subQuery) use ($search, $hasShortDescription): void {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");

                if ($hasShortDescription) {
                    $subQuery->orWhere('short_description', 'like', "%{$search}%");
                }
            });
        }

        if ($request->filled('product_type') && $hasProductType) {
            $productType = $request->string('product_type')->trim();
            if ($productType !== '') {
                $query->where('product_type', $productType);
            }
        }

        if ($request->filled('category')) {
            $category = $request->input('category');
            if (is_numeric($category)) {
                $query->where('category_id', $category);
            } else {
                $query->whereHas('category', function ($subQuery) use ($category): void {
                    $subQuery->where('slug', $category);
                });
            }
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->input('price_min'));
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->input('price_max'));
        }

        switch ($request->string('sort')->trim()) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderByDesc('id');
                break;
        }

        $products = $query->get();

        if (! $request->bearerToken()) {
            $products->makeHidden('download_file');
        }

        return new JsonResponse([
            'data' => $products,
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
                ->get();
            $payload['product_name'] = $product->name;
        } else {
            unset($payload['download_file']);
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

        if ($request->hasFile('gallery_images')) {
            $galleryFiles = $request->file('gallery_images');
            $paths = [];
            foreach ($galleryFiles as $file) {
                $paths[] = $file->store('products/gallery', 'public');
            }
            $data['gallery_images'] = $paths;
        } elseif (array_key_exists('gallery_images', $data) && is_string($data['gallery_images'])) {
            $data['gallery_images'] = json_decode($data['gallery_images'], true);
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

        if ($request->hasFile('gallery_images')) {
            $galleryFiles = $request->file('gallery_images');
            $paths = $product->gallery_images ?? [];
            foreach ($galleryFiles as $file) {
                $paths[] = $file->store('products/gallery', 'public');
            }
            $data['gallery_images'] = $paths;
        } elseif (array_key_exists('gallery_images', $data) && is_string($data['gallery_images'])) {
            $data['gallery_images'] = json_decode($data['gallery_images'], true);
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

    public function download(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'ip_address' => ['nullable', 'ip'],
        ]);

        $product = Product::query()->findOrFail($id);

        if (! $product->status) {
            return new JsonResponse([
                'success' => false,
                'message' => 'This product is not available for download.',
            ], 403);
        }

        if (! $product->download_file || ! Storage::disk('public')->exists($product->download_file)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Download file not found.',
            ], 404);
        }

        if (Schema::hasColumn('products', 'product_type') && $product->product_type !== 'free') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Paid downloads require a completed purchase before download.',
            ], 403);
        }

        $email = strtolower(trim($data['email']));
        $ip = $data['ip_address'] ? (string) $data['ip_address'] : (string) $request->ip();

        $downloadCount = ProductDownload::query()
            ->where('product_id', $product->id)
            ->where('email', $email)
            ->where('ip_address', $ip)
            ->count();

        if ($downloadCount >= self::DOWNLOAD_LIMIT_PER_EMAIL_IP) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Download limit exceeded.',
            ], 403);
        }

        DB::transaction(function () use ($product, $ip, $email, $request): void {
            $downloadData = [
                'product_id' => $product->id,
                'email' => $email,
                'ip_address' => $ip,
                'download_type' => 'free',
                'download_count' => 1,
                'downloaded_at' => now(),
            ];

            if (Schema::hasColumn('product_downloads', 'user_id')) {
                $downloadData['user_id'] = $request->user()?->id;
            }
            if (Schema::hasColumn('product_downloads', 'user_agent')) {
                $downloadData['user_agent'] = $request->header('User-Agent');
            }
            if (Schema::hasColumn('product_downloads', 'product_type')) {
                $downloadData['product_type'] = $product->product_type;
            }
            if (Schema::hasColumn('product_downloads', 'action_type')) {
                $downloadData['action_type'] = 'download';
            }

            ProductDownload::query()->create($downloadData);

            $product->increment('download_count');
        });

        $downloadUrl = URL::temporarySignedRoute(
            'products.download-file',
            now()->addMinutes(self::DOWNLOAD_ROUTE_EXPIRY_MINUTES),
            ['id' => $product->id]
        );

        return new JsonResponse([
            'success' => true,
            'download_url' => $downloadUrl,
        ]);
    }

    public function downloadFile(Request $request, int $id): JsonResponse|StreamedResponse
    {
        if (! $request->hasValidSignature()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid or expired download link.',
            ], 403);
        }

        $product = Product::query()->findOrFail($id);

        if (! $product->status || ! $product->download_file || ! Storage::disk('public')->exists($product->download_file)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Download file not available.',
            ], 404);
        }

        return Storage::disk('public')->download($product->download_file, basename($product->download_file));
    }

    public function histories(Request $request): JsonResponse
    {
        $query = ProductDownload::query()->with('product:id,name');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('email')) {
            $query->where('email', $request->string('email')->trim());
        }

        if ($request->filled('from')) {
            $query->where('downloaded_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('downloaded_at', '<=', $request->input('to'));
        }

        return new JsonResponse([
            'data' => $query->orderByDesc('downloaded_at')->paginate(50),
        ]);
    }

    public function historiesByIp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ip_address' => ['required', 'ip'],
        ]);

        $download = ProductDownload::query()
            ->where('ip_address', $data['ip_address'])
            ->first();

        return new JsonResponse([
            'email' => $download?->email,
            'ip_address' => $download ? $data['ip_address'] : null,
        ]);
    }

    private function validated(Request $request, bool $update): array
    {
        $data = $request->validate([
            'name' => [$update ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug' => array_merge($update ? ['sometimes'] : ['required'], ['string', 'max:255']),
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'product_type' => ['nullable', Rule::in(['free', 'paid'])],
            'price' => ['nullable', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'status' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:portfolio_categories,id'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_keywords' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
            'is_best_seller' => ['nullable', 'boolean'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['file', 'image', 'max:5120'],
            'view_count' => ['nullable', 'integer', 'min:0'],
            'sales_count' => ['nullable', 'integer', 'min:0'],
            'primary_image' => ['nullable', 'file', 'image', 'max:5120'],
            'download_file' => [$update ? 'nullable' : 'required', 'file', 'max:51200'],
        ]);

        if (array_key_exists('status', $data)) {
            $data['status'] = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);
        }

        return $data;
    }
}
