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
        if ($request->hasSession() && $request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        } elseif ($request->hasCookie('locale') && in_array($request->cookie('locale'), self::SUPPORTED)) {
            $locale = $request->cookie('locale');
            if ($request->hasSession()) {
                $request->session()->put('locale', $locale);
            }
        } else {
            $header = $request->header('Accept-Language');
            $locale = $header
                ? ($request->getPreferredLanguage(self::SUPPORTED) ?? config('app.locale'))
                : config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
