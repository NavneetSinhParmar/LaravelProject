<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PortfolioCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $items = PortfolioCategory::query()
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get();
        } catch (\Throwable $e) {
            return new JsonResponse([ 'data' => [], 'error' => 'Categories unavailable.' ], 200);
        }

        return new JsonResponse([ 'data' => $items ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page_slug' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
            'slug' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        try {
            if ($request->hasFile('logo')) {
                $validated['logo'] = $request->file('logo')->store('categories', 'public');
            }

            if (empty($validated['slug']) && ! empty($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $cat = PortfolioCategory::query()->create($validated);
        } catch (\Throwable $e) {
            return new JsonResponse([ 'message' => 'Save failed.', 'error' => $e->getMessage() ], 500);
        }

        return new JsonResponse([ 'message' => 'Category created.', 'data' => $cat ], 201);
    }

    public function show(PortfolioCategory $category): JsonResponse
    {
        return new JsonResponse([ 'data' => $category ]);
    }

    public function update(Request $request, PortfolioCategory $category): JsonResponse
    {
        $isMultipart = $request->isMethod('POST');

        $validated = $request->validate([
            'page_slug' => ['nullable', 'string', 'max:255'],
            'name' => [$isMultipart ? 'sometimes' : 'required', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
            'slug' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        try {
            if ($request->hasFile('logo')) {
                if ($category->logo) {
                    Storage::disk('public')->delete($category->logo);
                }
                $validated['logo'] = $request->file('logo')->store('categories', 'public');
            }

            if ($request->filled('slug')) {
                $validated['slug'] = Str::slug($request->string('slug'));
            } elseif ($request->filled('name')) {
                $validated['slug'] = Str::slug($request->string('name'));
            }

            $category->update($validated);
        } catch (\Throwable $e) {
            return new JsonResponse([ 'message' => 'Update failed.', 'error' => $e->getMessage() ], 500);
        }

        return new JsonResponse([ 'message' => 'Category updated.', 'data' => $category->fresh() ]);
    }

    public function destroy(PortfolioCategory $category): JsonResponse
    {
        try {
            if ($category->logo) {
                Storage::disk('public')->delete($category->logo);
            }
            $category->delete();
        } catch (\Throwable $e) {
            return new JsonResponse([ 'message' => 'Delete failed.', 'error' => $e->getMessage() ], 500);
        }

        return new JsonResponse([ 'message' => 'Category deleted.' ]);
    }
}
