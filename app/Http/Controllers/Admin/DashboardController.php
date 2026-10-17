<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DetailingStatus;
use App\Http\Controllers\Controller;
use App\Models\AutomotiveDetailing;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $counts = AutomotiveDetailing::toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'counts' => collect(DetailingStatus::cases())
                ->mapWithKeys(fn (DetailingStatus $status) => [$status->value => (int) ($counts[$status->value] ?? 0)]),
            'pending' => AutomotiveDetailing::where('status', DetailingStatus::Pending)
                ->with('owner')
                ->oldest()
                ->limit(10)
                ->get(),
        ]);
    }
}
