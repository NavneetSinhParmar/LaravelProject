<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageSlug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageSlugController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $items = PageSlug::query()
                ->orderByDesc('id')
                ->get();
        } catch (\Throwable $e) {
            return new JsonResponse([
                'data' => [],
                'error' => 'Page slugs unavailable.'
            ], 200);
        }

        return new JsonResponse([
            'data' => $items
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            if (empty($validated['slug']) && !empty($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $pageSlug = PageSlug::query()->create($validated);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => 'Save failed.',
                'error' => $e->getMessage()
            ], 500);
        }

        return new JsonResponse([
            'message' => 'Page slug created.',
            'data' => $pageSlug
        ], 201);
    }

    public function show(PageSlug $pageSlug): JsonResponse
    {
        return new JsonResponse([
            'data' => $pageSlug
        ]);
    }

    public function update(Request $request, PageSlug $pageSlug): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            if ($request->filled('slug')) {
                $validated['slug'] = Str::slug($request->string('slug'));
            } elseif ($request->filled('name')) {
                $validated['slug'] = Str::slug($request->string('name'));
            }

            $pageSlug->update($validated);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => 'Update failed.',
                'error' => $e->getMessage()
            ], 500);
        }

        return new JsonResponse([
            'message' => 'Page slug updated.',
            'data' => $pageSlug->fresh()
        ]);
    }

    public function destroy(PageSlug $pageSlug): JsonResponse
    {
        try {
            $pageSlug->delete();
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => 'Delete failed.',
                'error' => $e->getMessage()
            ], 500);
        }

        return new JsonResponse([
            'message' => 'Page slug deleted.'
        ]);
    }
}