<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessHoursRequest;
use App\Models\BusinessHour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BusinessHoursController extends Controller
{
    public function edit(Request $request): View
    {
        $detailing = $request->user()->automotiveDetailing;

        Gate::authorize('update', $detailing);

        return view('owner.hours.edit', [
            'hours' => $detailing->businessHours->keyBy('day_of_week'),
        ]);
    }

    public function update(BusinessHoursRequest $request): RedirectResponse
    {
        $detailing = $request->user()->automotiveDetailing;

        Gate::authorize('update', $detailing);

        BusinessHour::upsert(
            array_map(fn (array $hours) => [...$hours, 'automotive_detailing_id' => $detailing->id], $request->businessHours()),
            ['automotive_detailing_id', 'day_of_week'],
            ['opens_at', 'closes_at', 'closed'],
        );

        return redirect()->route('owner.hours.edit')->with('status', 'Horários de funcionamento atualizados.');
    }
}
