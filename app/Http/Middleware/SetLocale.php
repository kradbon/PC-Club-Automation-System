<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['en', 'ru', 'tj'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->route('locale');

        abort_unless(in_array($locale, self::SUPPORTED, true), 404);

        app()->setLocale($locale);
        session(['locale' => $locale]);
        URL::defaults(['locale' => $locale]);

        if ($user = $request->user()) {
            if ($user->preferred_locale !== $locale) {
                $user->forceFill(['preferred_locale' => $locale])->saveQuietly();
            }
        }

        return $next($request);
    }
}
