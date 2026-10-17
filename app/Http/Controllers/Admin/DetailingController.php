<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DetailingStatus;
use App\Http\Controllers\Controller;
use App\Models\AutomotiveDetailing;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DetailingController extends Controller
{
    public function index(Request $request): View
    {
        $status = DetailingStatus::tryFrom($request->string('status')->value());

        return view('admin.detailings.index', [
            'status' => $status,
            'detailings' => AutomotiveDetailing::query()
                ->with('owner')
                ->when($status, fn ($query) => $query->where('status', $status))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }
}
