<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromHeader
{
    private const SUPPORTED = ['pt', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Accept-Language');

        if ($header) {
            $locale = $request->getPreferredLanguage(self::SUPPORTED) ?? config('app.locale');
            App::setLocale($locale);
        }

        return $next($request);
    }
}
