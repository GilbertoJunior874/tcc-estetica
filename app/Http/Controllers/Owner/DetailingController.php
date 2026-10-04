<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\DetailingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DetailingController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->automotiveDetailing) {
            return redirect()->route('owner.dashboard');
        }

        return view('owner.detailing.create');
    }

    public function store(DetailingRequest $request): RedirectResponse
    {
        $request->user()->automotiveDetailing()->create($request->validated());

        return redirect()->route('owner.dashboard')
            ->with('status', 'Solicitação de cadastro enviada para análise.');
    }
}
