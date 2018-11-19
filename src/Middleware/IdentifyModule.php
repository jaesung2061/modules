<?php

namespace Caffeinated\Modules\Middleware;

use Closure;

class IdentifyModule
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param array $arguments
     *
     * @return mixed
     */
    public function handle($request, Closure $next, ...$arguments)
    {
        $slug = $arguments[0];
        $location = $arguments[1] ?? null;
        $module = modules($location)->where('slug', $slug);

        $request->session()->flash('module', $module);

        return $next($request);
    }
}
