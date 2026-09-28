<?php

namespace App\Http\Controllers;

use App\Models\AutomotiveDetailing;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'featured' => AutomotiveDetailing::approved()
                ->withCatalogSummary()
                ->with(['activeServices' => fn ($query) => $query->orderBy('price_cents')])
                ->inRandomOrder()
                ->limit(3)
                ->get(),
            'totalApproved' => AutomotiveDetailing::approved()->count(),
        ]);
    }
}
