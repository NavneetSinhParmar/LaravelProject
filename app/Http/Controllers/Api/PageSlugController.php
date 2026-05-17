<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageSlug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageSlugController extends Controller
{
    private function makeUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($baseSlug);
        $slug = $baseSlug !== '' ? $baseSlug : 'page';

        $query = PageSlug::query();
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if (! $query->where('slug', $slug)->exists()) {
            return $slug;
        }

        $i = 2;
        while (PageSlug::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug.'-'.$i)
            ->exists()) {
            $i++;
        }

        return $slug.'-'.$i;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $items = PageSlug::query()
                ->orderBy('name')
                ->orderByDesc('id')
                ->get();
        } catch (\Throwable $e) {
            return new JsonResponse([
                'data' => [],
                'error' => 'Page slugs unavailable.',
            ], 200);
        }

        return new JsonResponse([
            'data' => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['slug'] = $this->makeUniqueSlug(
            empty($validated['slug']) ? $validated['name'] : $validated['slug']
        );

        try {
            $pageSlug = PageSlug::query()->create($validated);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => 'Save failed.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return new JsonResponse([
            'message' => 'Page slug created.',
            'data' => $pageSlug,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return new JsonResponse([
            'data' => PageSlug::query()->findOrFail($id),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $pageSlug = PageSlug::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        $seed = empty($validated['slug'] ?? null)
            ? $validated['name']
            : $validated['slug'];

        $validated['slug'] = $this->makeUniqueSlug($seed, $pageSlug->id);

        try {
            $pageSlug->update($validated);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => 'Update failed.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return new JsonResponse([
            'message' => 'Page slug updated.',
            'data' => $pageSlug->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $pageSlug = PageSlug::query()->findOrFail($id);

        try {
            $pageSlug->delete();
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => 'Delete failed.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return new JsonResponse([
            'message' => 'Page slug deleted.',
        ]);
    }
}
