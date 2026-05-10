<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Faq::query();

        if ($request->filled('pageslug')) {
            $q->where('pageslug', $request->string('pageslug'));
        }

        if ($request->filled('is_featured')) {
            $q->where('is_featured', (bool) $request->boolean('is_featured'));
        }

        $items = $q->orderBy('order')->orderByDesc('id')->get();

        return new JsonResponse(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pageslug' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'question' => ['required', 'string', 'max:1000'],
            'answer' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $item = Faq::create($validated);

        return new JsonResponse(['message' => 'Created.', 'data' => $item], 201);
    }

    public function show(Faq $faq): JsonResponse
    {
        return new JsonResponse(['data' => $faq]);
    }

    public function update(Request $request, Faq $faq): JsonResponse
    {
        $validated = $request->validate([
            'pageslug' => ['sometimes', 'required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'question' => ['sometimes', 'required', 'string', 'max:1000'],
            'answer' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $faq->update($validated);

        return new JsonResponse(['message' => 'Updated.', 'data' => $faq->fresh()]);
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();
        return new JsonResponse(['message' => 'Deleted.']);
    }
}
