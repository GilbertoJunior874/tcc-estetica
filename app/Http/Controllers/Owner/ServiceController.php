<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $detailing = $request->user()->automotiveDetailing;

        return view('owner.services.index', [
            'detailing' => $detailing,
            'services' => $detailing->services()->orderByDesc('active')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Service::class);

        return view('owner.services.create');
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        Gate::authorize('create', Service::class);

        $request->user()->automotiveDetailing->services()->create($request->serviceData());

        return redirect()->route('owner.services.index')->with('status', 'Serviço cadastrado.');
    }

    public function edit(Service $service): View
    {
        Gate::authorize('update', $service);

        return view('owner.services.edit', ['service' => $service]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        Gate::authorize('update', $service);

        $service->update($request->serviceData());

        return redirect()->route('owner.services.index')->with('status', 'Serviço atualizado.');
    }

    public function toggle(Service $service): RedirectResponse
    {
        Gate::authorize('update', $service);

        $service->update(['active' => ! $service->active]);

        return redirect()->route('owner.services.index')
            ->with('status', $service->active ? 'Serviço ativado.' : 'Serviço desativado.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        Gate::authorize('delete', $service);

        $service->delete();

        return redirect()->route('owner.services.index')->with('status', 'Serviço removido.');
    }
}
