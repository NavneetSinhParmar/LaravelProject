<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    // ─────────────────────────────────────────────
    //  HELPER: save an uploaded image file
    //  Returns the relative path  e.g. "sliders/1234_photo.jpg"
    //  that is stored in the DB column.
    // ─────────────────────────────────────────────
    private function saveImage(\Illuminate\Http\UploadedFile $file): string
    {
        $filename  = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
        $subDir    = 'sliders';

        // ── Primary: Laravel Storage disk "public" ──────────────────────────
        // Works when storage:link exists OR when filesystems.php maps
        // 'public' disk root to public_html/laravel_api/public/storage
        try {
            $path = $file->storeAs($subDir, $filename, 'public');
            if ($path && Storage::disk('public')->exists($path)) {
                return $path;                          // "sliders/filename.jpg"
            }
        } catch (\Throwable $e) {
            // fall through to direct-write fallback
        }

        // ── Fallback: write directly into public/storage/sliders ────────────
        // On GoDaddy shared hosting the symlink often can't be created, so we
        // write straight into the publicly-accessible folder.
        $publicDir = public_path('storage/' . $subDir);

        if (! is_dir($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $file->move($publicDir, $filename);

        return $subDir . '/' . $filename;              // "sliders/filename.jpg"
    }

    // ─────────────────────────────────────────────
    //  HELPER: delete an image by its stored path
    // ─────────────────────────────────────────────
    private function deleteImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        // Remove from Storage disk
        Storage::disk('public')->delete($path);

        // Remove from direct public/storage fallback
        $abs = public_path('storage/' . ltrim($path, '/'));
        if (file_exists($abs)) {
            @unlink($abs);
        }
    }

    // ─────────────────────────────────────────────
    //  HELPER: turn a stored path into a full URL
    //  The frontend just does:  slider.image_url
    //  and never has to guess the base URL.
    // ─────────────────────────────────────────────
    private function withImageUrl(Slider $slider): array
    {
        $data = $slider->toArray();
        $data['image_url'] = $slider->image
            ? asset('storage/' . ltrim($slider->image, '/'))
            : null;
        return $data;
    }

    // ─────────────────────────────────────────────
    //  INDEX
    // ─────────────────────────────────────────────
    public function index(): JsonResponse
    {
        $rows = Slider::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn($s) => $this->withImageUrl($s));

        return new JsonResponse(['data' => $rows]);
    }

    // ─────────────────────────────────────────────
    //  SHOW
    // ─────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $slider = Slider::query()->findOrFail($id);

        return new JsonResponse(['data' => $this->withImageUrl($slider)]);
    }

    // ─────────────────────────────────────────────
    //  STORE
    // ─────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'page_slug'    => ['required', 'string', 'max:255'],
            'section_key'  => ['required', 'string', 'max:255'],
            'title'        => ['nullable', 'string', 'max:255'],
            'subtitle'     => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'html_content' => ['nullable', 'string'],
            'link'         => ['nullable', 'string', 'max:255'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
            'image'        => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $this->saveImage($request->file('image'));
        }

        $slider = Slider::query()->create($data);

        return new JsonResponse([
            'message' => 'Slider created.',
            'data'    => $this->withImageUrl($slider),
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  UPDATE  (PUT = no file | POST = with file)
    // ─────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $slider = Slider::query()->findOrFail($id);

        $data = $request->validate([
            'page_slug'    => ['required', 'string', 'max:255'],
            'section_key'  => ['required', 'string', 'max:255'],
            'title'        => ['nullable', 'string', 'max:255'],
            'subtitle'     => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'html_content' => ['nullable', 'string'],
            'link'         => ['nullable', 'string', 'max:255'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
            'image'        => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            $this->deleteImage($slider->image);          // remove old file
            $data['image'] = $this->saveImage($request->file('image'));
        }

        $slider->update($data);

        return new JsonResponse([
            'message' => 'Slider updated.',
            'data'    => $this->withImageUrl($slider->fresh()),
        ]);
    }

    // ─────────────────────────────────────────────
    //  DESTROY
    // ─────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $slider = Slider::query()->findOrFail($id);
        $this->deleteImage($slider->image);
        $slider->delete();

        return new JsonResponse(['message' => 'Slider deleted.']);
    }
}