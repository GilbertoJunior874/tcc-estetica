<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $detailing = $request->user()->automotiveDetailing?->loadCount([
            'services',
            'services as active_services_count' => fn ($query) => $query->where('active', true),
        ]);

        return view('owner.dashboard', ['detailing' => $detailing]);
    }
}
