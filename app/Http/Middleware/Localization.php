<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language');

        if ($locale) {
            // Get the first two characters (e.g. 'en', 'es')
            $lang = substr($locale, 0, 2);
            
            if (in_array($lang, ['en', 'es'])) {
                App::setLocale($lang);
            }
        }

        return $next($request);
    }
}
