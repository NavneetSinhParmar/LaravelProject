<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        return response()->json(Slider::orderBy('sort_order')->get());
    }

    public function show($id)
    {
        $slider = Slider::findOrFail($id);
        return response()->json($slider);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'page_slug' => 'required|string',
            'section_key' => 'required|string',
            'title' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'html_content' => 'nullable|string',
            'link' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|file|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('sliders', 'public');
            $data['image'] = $path;
        }

        $slider = Slider::create($data);

        return response()->json($slider, 201);
    }

    public function update(Request $request, $id)
    {
        $slider = Slider::findOrFail($id);

        $data = $request->validate([
            'page_slug' => 'required|string',
            'section_key' => 'required|string',
            'title' => 'nullable|string',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'html_content' => 'nullable|string',
            'link' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|file|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            if ($slider->image) {
                Storage::disk('public')->delete($slider->image);
            }
            $path = $request->file('image')->store('sliders', 'public');
            $data['image'] = $path;
        }

        $slider->update($data);

        return response()->json($slider);
    }

    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);
        if ($slider->image) {
            Storage::disk('public')->delete($slider->image);
        }
        $slider->delete();

        return response()->json(null, 204);
    }
}
