<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clients;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientsController extends Controller
{
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'data' => Clients::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function show(Clients $client): JsonResponse
    {
        return new JsonResponse([
            'data' => $client,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, false);
        unset($data['logo']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('clients', 'public');
        }

        $clients = Clients::query()->create($data);

        return new JsonResponse([
            'message' => 'Client created.',
            'data' => $clients,
        ], 201);
    }

    public function update(Request $request, Clients $client): JsonResponse
    {
        $data = $this->validated($request, true);
        unset($data['logo']);

        if ($request->hasFile('logo')) {
            if ($client->logo) {
                Storage::disk('public')->delete($client->logo);
            }
            $data['logo'] = $request->file('logo')->store('clients', 'public');
        }

        $client->update($data);

        return new JsonResponse([
            'message' => 'Client updated.',
            'data' => $client->fresh(),
        ]);
    }

    public function destroy(Clients $client): JsonResponse
    {
        if ($client->logo) {
            Storage::disk('public')->delete($client->logo);
        }
        $client->delete();

        return new JsonResponse([
            'message' => 'Client deleted.',
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
            'logo' => ['nullable', 'file', 'image', 'max:5120'],
            'link' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
