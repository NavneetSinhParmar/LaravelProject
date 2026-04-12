<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonials;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialsController extends Controller
{
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'data' => Testimonials::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function show(Testimonials $testimonial): JsonResponse
    {
        return new JsonResponse([
            'data' => $testimonial,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, false);
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('testimonials', 'public');
        }

        $testimonial = Testimonials::query()->create($data);

        return new JsonResponse([
            'message' => 'Testimonial created.',
            'data' => $testimonial,
        ], 201);
    }

    public function update(Request $request, Testimonials $testimonial): JsonResponse
    {
        $data = $this->validated($request, true);
        unset($data['image']);

        if ($request->hasFile('image')) {
            if ($testimonial->image) {
                Storage::disk('public')->delete($testimonial->image);
            }
            $data['image'] = $request->file('image')->store('testimonials', 'public');
        }

        $testimonial->update($data);

        return new JsonResponse([
            'message' => 'Testimonial updated.',
            'data' => $testimonial->fresh(),
        ]);
    }

    public function destroy(Testimonials $testimonial): JsonResponse
    {
        if ($testimonial->image) {
            Storage::disk('public')->delete($testimonial->image);
        }
        $testimonial->delete();

        return new JsonResponse([
            'message' => 'Testimonial deleted.',
        ]);
    }

    private function validated(Request $request, bool $update): array
    {
        $pageSlug = $update
            ? ['sometimes', 'required', 'string', 'max:255']
            : ['required', 'string', 'max:255'];
        $nameRule = $update
            ? ['sometimes', 'required', 'string', 'max:255']
            : ['required', 'string', 'max:255'];

        return $request->validate([
            'page_slug' => $pageSlug,
            'name' => $nameRule,
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'designation' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
