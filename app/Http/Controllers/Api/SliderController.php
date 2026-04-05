<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'data' => Slider::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return new JsonResponse([
            'data' => Slider::query()->findOrFail($id),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'page_slug' => ['required', 'string', 'max:255'],
            'section_key' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'html_content' => ['nullable', 'string'],
            'link' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('sliders', 'public');
            $data['image'] = $path;
        }

        $slider = Slider::query()->create($data);

        return new JsonResponse([
            'message' => 'Slider created.',
            'data' => $slider,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $slider = Slider::query()->findOrFail($id);

        $data = $request->validate([
            'page_slug' => ['required', 'string', 'max:255'],
            'section_key' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'html_content' => ['nullable', 'string'],
            'link' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            if ($slider->image) {
                Storage::disk('public')->delete($slider->image);
            }
            $path = $request->file('image')->store('sliders', 'public');
            $data['image'] = $path;
        }

        $slider->update($data);

        return new JsonResponse([
            'message' => 'Slider updated.',
            'data' => $slider->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $slider = Slider::query()->findOrFail($id);
        if ($slider->image) {
            Storage::disk('public')->delete($slider->image);
        }
        $slider->delete();

        return new JsonResponse([
            'message' => 'Slider deleted.',
        ]);
    }
}
