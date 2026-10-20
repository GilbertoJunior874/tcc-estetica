<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogFilterRequest;
use App\Models\AutomotiveDetailing;
use Illuminate\View\View;

class AutomotiveDetailingController extends Controller
{
    public function index(CatalogFilterRequest $request): View
    {
        $filters = $request->validated();

        return view('detailings.index', [
            'filters' => $filters,
            'cities' => AutomotiveDetailing::approved()->distinct()->orderBy('city')->pluck('city'),
            'detailings' => AutomotiveDetailing::approved()
                ->search($filters['q'] ?? null)
                ->when($filters['cidade'] ?? null, fn ($query, string $city) => $query->where('city', $city))
                ->offering(
                    $filters['servico'] ?? null,
                    isset($filters['preco_min']) ? $filters['preco_min'] * 100 : null,
                    isset($filters['preco_max']) ? $filters['preco_max'] * 100 : null,
                )
                ->withCatalogSummary()
                ->with(['activeServices' => fn ($query) => $query->orderBy('price_cents')])
                ->orderBy('name')
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function show(AutomotiveDetailing $detailing): View
    {
        abort_unless($detailing->isApproved(), 404);

        $detailing->load(['activeServices' => fn ($query) => $query->orderBy('price_cents'), 'businessHours']);

        return view('detailings.show', ['detailing' => $detailing]);
    }
}
