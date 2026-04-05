<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsPageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'page' => ['required', 'string'],
            'status' => ['nullable', 'boolean'],
        ]);

        $query = CmsPage::query()->where('page_slug', $data['page']);

        if (array_key_exists('status', $data)) {
            $query->where('status', $data['status']);
        }

        return new JsonResponse([
            'data' => $query->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());
        $cmsPage = CmsPage::query()->create($data);

        return new JsonResponse([
            'message' => 'CMS section created.',
            'data' => $cmsPage,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $cmsPage = CmsPage::query()->findOrFail($id);
        $data = $request->validate($this->rules());
        $cmsPage->update($data);

        return new JsonResponse([
            'message' => 'CMS section updated.',
            'data' => $cmsPage->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        CmsPage::query()->findOrFail($id)->delete();

        return new JsonResponse([
            'message' => 'CMS section deleted.',
        ]);
    }

    private function rules(): array
    {
        return [
            'page_slug' => ['required', 'string', 'max:100'],
            'section_key' => ['required', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:255'],
            'json_data' => ['nullable', 'array'],
            'status' => ['required', 'boolean'],
        ];
    }
}
