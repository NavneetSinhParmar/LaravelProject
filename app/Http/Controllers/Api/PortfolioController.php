<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PortfolioController extends Controller
{
    public function categories(): JsonResponse
    {
        return new JsonResponse([
            'data' => PortfolioCategory::query()
                ->where('status', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
        ]);
    }

    public function index(): JsonResponse
    {
        return new JsonResponse([
            'data' => Portfolio::query()
                ->with('category')
                ->orderBy('order')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePortfolio($request, false);
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('portfolios', 'public');
        }

        $data['slug'] = $this->resolveSlug($request);

        $portfolio = Portfolio::query()->create($data);

        return new JsonResponse([
            'message' => 'Portfolio created.',
            'data' => $portfolio->load('category'),
        ], 201);
    }

    public function show(Portfolio $portfolio): JsonResponse
    {
        return new JsonResponse([
            'data' => $portfolio->load('category'),
        ]);
    }

    public function update(Request $request, Portfolio $portfolio): JsonResponse
    {
        $data = $this->validatePortfolio($request, true);
        unset($data['image']);

        if ($request->hasFile('image')) {
            if ($portfolio->image) {
                Storage::disk('public')->delete($portfolio->image);
            }
            $data['image'] = $request->file('image')->store('portfolios', 'public');
        }

        if ($request->filled('slug')) {
            $data['slug'] = Str::slug($request->string('slug'));
        } elseif ($request->filled('title')) {
            $data['slug'] = Str::slug($request->string('title'));
        }

        $portfolio->update($data);

        return new JsonResponse([
            'message' => 'Portfolio updated.',
            'data' => $portfolio->fresh()->load('category'),
        ]);
    }

    public function destroy(Portfolio $portfolio): JsonResponse
    {
        if ($portfolio->image) {
            Storage::disk('public')->delete($portfolio->image);
        }
        $portfolio->delete();

        return new JsonResponse([
            'message' => 'Portfolio deleted.',
        ]);
    }

    private function validatePortfolio(Request $request, bool $update): array
    {
        $titleRule = $update
            ? ['sometimes', 'required', 'string', 'max:255']
            : ['required', 'string', 'max:255'];

        $validated = $request->validate([
            'title' => $titleRule,
            'slug' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'link' => ['nullable', 'string', 'max:500'],
            'page_slug' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', Rule::exists('portfolio_categories', 'id')],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'status' => ['nullable', 'boolean'],
            'json_data' => ['nullable'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        return $this->normalizeJsonDataField($validated);
    }

    private function normalizeJsonDataField(array $data): array
    {
        if (! array_key_exists('json_data', $data)) {
            return $data;
        }

        $raw = $data['json_data'];
        if ($raw === null || $raw === '') {
            $data['json_data'] = null;

            return $data;
        }

        if (is_array($raw)) {
            return $data;
        }

        $decoded = json_decode((string) $raw, true);
        $data['json_data'] = json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : null;

        return $data;
    }

    private function resolveSlug(Request $request): string
    {
        if ($request->filled('slug')) {
            return Str::slug($request->string('slug'));
        }

        if ($request->filled('title')) {
            $base = Str::slug($request->string('title'));

            return $base !== '' ? $base : 'portfolio-'.Str::lower(Str::random(8));
        }

        return 'portfolio-'.Str::lower(Str::random(8));
    }
}
