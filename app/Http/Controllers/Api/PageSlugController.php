<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageSlug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageSlugController extends Controller
{
    // Public index for dropdowns
    public function index(Request $request): JsonResponse
    {
        $items = PageSlug::query()->orderBy('name')->get();
        return new JsonResponse(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $item = PageSlug::create($validated);

        return new JsonResponse(['message' => 'Created.', 'data' => $item], 201);
    }

    public function show(PageSlug $pageSlug): JsonResponse
    {
        return new JsonResponse(['data' => $pageSlug]);
    }

    public function update(Request $request, PageSlug $pageSlug): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        if (isset($validated['name']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $pageSlug->update($validated);

        return new JsonResponse(['message' => 'Updated.', 'data' => $pageSlug->fresh()]);
    }

    public function destroy(PageSlug $pageSlug): JsonResponse
    {
        $pageSlug->delete();
        return new JsonResponse(['message' => 'Deleted.']);
    }
}
