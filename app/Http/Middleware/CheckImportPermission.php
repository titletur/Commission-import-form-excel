<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckImportPermission
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->id === 1 || Auth::user()->id === 1)) {
            return $next($request);
        }

        return redirect()->back()->with('no_permission', "You dont have permissions to access this page.");

    }
}
