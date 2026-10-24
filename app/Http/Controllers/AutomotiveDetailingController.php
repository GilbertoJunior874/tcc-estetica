<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogFilterRequest;
use App\Models\AutomotiveDetailing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomotiveDetailingController extends Controller
{
    public function index(CatalogFilterRequest $request): View
    {
        $filters = $request->validated();
        $hasLocation = isset($filters['lat'], $filters['lng']);
        $sort = $filters['ordem'] ?? ($hasLocation ? 'distancia' : 'nome');

        if ($sort === 'distancia' && ! $hasLocation) {
            $sort = 'nome';
        }

        return view('detailings.index', [
            'filters' => $filters,
            'hasLocation' => $hasLocation,
            'sort' => $sort,
            'cities' => AutomotiveDetailing::approved()->distinct()->orderBy('city')->pluck('city'),
            'detailings' => AutomotiveDetailing::approved()
                ->search($filters['q'] ?? null)
                ->when($filters['cidade'] ?? null, fn ($query, string $city) => $query->where('city', $city))
                ->offering(
                    $filters['servico'] ?? null,
                    isset($filters['preco_min']) ? $filters['preco_min'] * 100 : null,
                    isset($filters['preco_max']) ? $filters['preco_max'] * 100 : null,
                )
                ->when($hasLocation, fn ($query) => $query->nearTo((float) $filters['lat'], (float) $filters['lng'], $filters['raio'] ?? null))
                ->withCatalogSummary()
                ->with(['activeServices' => fn ($query) => $query->orderBy('price_cents'), 'businessHours'])
                ->when($sort === 'distancia', fn ($query) => $query->orderBy('distance_km'))
                ->when($sort === 'preco', fn ($query) => $query->orderBy('active_services_min_price_cents'))
                ->orderBy('name')
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function whatsapp(Request $request, AutomotiveDetailing $detailing): RedirectResponse
    {
        abort_unless($detailing->isApproved() && $detailing->whatsapp_number, 404);

        $data = $request->validate([
            'servicos' => ['nullable', 'array', 'max:50'],
            'servicos.*' => ['integer'],
        ]);

        $services = $detailing->activeServices()
            ->whereIn('id', $data['servicos'] ?? [])
            ->orderBy('price_cents')
            ->get();

        return redirect()->away($detailing->whatsappUrlFor($services));
    }

    public function show(AutomotiveDetailing $detailing): View
    {
        abort_unless($detailing->isApproved(), 404);

        $detailing->load(['activeServices' => fn ($query) => $query->orderBy('price_cents'), 'businessHours']);

        return view('detailings.show', ['detailing' => $detailing]);
    }
}
