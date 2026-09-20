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

}
