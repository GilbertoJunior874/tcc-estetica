<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DetailingStatus;
use App\Http\Controllers\Controller;
use App\Models\AutomotiveDetailing;
use Illuminate\Http\RedirectResponse;
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

    public function show(AutomotiveDetailing $detailing): View
    {
        $detailing->load(['owner', 'reviewer', 'businessHours', 'services' => fn ($query) => $query->orderBy('name')]);

        return view('admin.detailings.show', ['detailing' => $detailing]);
    }

    public function approve(Request $request, AutomotiveDetailing $detailing): RedirectResponse
    {
        return $this->transition($request, $detailing, DetailingStatus::Approved, 'Estética aprovada e publicada no catálogo.');
    }

    public function reject(Request $request, AutomotiveDetailing $detailing): RedirectResponse
    {
        $data = $request->validate(
            ['rejection_reason' => ['required', 'string', 'min:10', 'max:1000']],
            attributes: ['rejection_reason' => 'motivo da recusa'],
        );

        return $this->transition($request, $detailing, DetailingStatus::Rejected, 'Solicitação recusada. O responsável verá o motivo no painel.', $data['rejection_reason']);
    }

    public function deactivate(Request $request, AutomotiveDetailing $detailing): RedirectResponse
    {
        return $this->transition($request, $detailing, DetailingStatus::Inactive, 'Estética desativada e removida do catálogo.');
    }

    public function reactivate(Request $request, AutomotiveDetailing $detailing): RedirectResponse
    {
        return $this->transition($request, $detailing, DetailingStatus::Approved, 'Estética reativada e publicada novamente.');
    }

    private function transition(Request $request, AutomotiveDetailing $detailing, DetailingStatus $status, string $message, ?string $reason = null): RedirectResponse
    {
        abort_unless($detailing->status->canTransitionTo($status), 409, 'Esta alteração de situação não é permitida.');

        $detailing->transitionTo($status, $request->user(), $reason);

        return redirect()->route('admin.detailings.show', $detailing)->with('status', $message);
    }
}
