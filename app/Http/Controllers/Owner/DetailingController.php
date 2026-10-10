<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\DetailingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

    public function edit(Request $request): View
    {
        $detailing = $request->user()->automotiveDetailing;

        Gate::authorize('update', $detailing);

        return view('owner.detailing.edit', ['detailing' => $detailing]);
    }

    public function update(DetailingRequest $request): RedirectResponse
    {
        $detailing = $request->user()->automotiveDetailing;

        Gate::authorize('update', $detailing);

        $detailing->update($request->validated());

        return redirect()->route('owner.detailing.edit')->with('status', 'Dados da estética atualizados.');
    }
}
