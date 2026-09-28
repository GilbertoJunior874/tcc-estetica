<?php

namespace App\Http\Controllers;

use App\Models\AutomotiveDetailing;
use Illuminate\View\View;

class AutomotiveDetailingController extends Controller
{
    public function index(): View
    {
        return view('detailings.index', [
            'detailings' => AutomotiveDetailing::approved()
                ->withCatalogSummary()
                ->with(['activeServices' => fn ($query) => $query->orderBy('price_cents')])
                ->orderBy('name')
                ->paginate(12),
        ]);
    }

    public function show(AutomotiveDetailing $detailing): View
    {
        abort_unless($detailing->isApproved(), 404);

        $detailing->load(['activeServices' => fn ($query) => $query->orderBy('price_cents')]);

        return view('detailings.show', ['detailing' => $detailing]);
    }
}
