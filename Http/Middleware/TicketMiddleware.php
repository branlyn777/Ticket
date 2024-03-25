<?php

namespace Modules\Ticket\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class TicketMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $slugModule = 'mod_ticket';
        $module = \App\Module::where('slug',$slugModule)->first();
        // Over
        if(Auth::user()->hasRole('superadmin')){
            return $next($request);
        }

        // if module not enable or not active return error
        if (!Auth::user()->moduleEnabled($slugModule) || $module->active == 0){
            return redirect()->route('home')->with('error', __('main.no_module_permission') );
        }
        // Can pass
        return $next($request);
    }
}
