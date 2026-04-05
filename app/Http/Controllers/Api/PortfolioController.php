<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'data' => Portfolio::query()->latest('id')->get(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return new JsonResponse([
            'data' => Portfolio::query()->findOrFail($id),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $portfolio = Portfolio::query()->create(
            $request->validate($this->rules())
        );

        return new JsonResponse([
            'message' => 'Portfolio created.',
            'data' => $portfolio,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $portfolio = Portfolio::query()->findOrFail($id);
        $portfolio->update($request->validate($this->rules()));

        return new JsonResponse([
            'message' => 'Portfolio updated.',
            'data' => $portfolio->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        Portfolio::query()->findOrFail($id)->delete();

        return new JsonResponse([
            'message' => 'Portfolio deleted.',
        ]);
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
            'link' => ['nullable', 'url', 'max:255'],
            'json_data' => ['nullable', 'array'],
            'status' => ['required', 'boolean'],
        ];
    }
}
