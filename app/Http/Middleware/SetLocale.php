<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (! array_key_exists($locale, config('app.supported_locales'))) {
            abort(404);
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);
        session(['locale' => $locale]);

        return $next($request);
    }
}
