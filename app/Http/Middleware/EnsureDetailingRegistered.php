<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDetailingRegistered
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->automotiveDetailing) {
            return redirect()->route('owner.detailing.create');
        }

        return $next($request);
    }
}
