<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class SystemAdminMiddleware
{
    
    public function handle($request, Closure $next)
    {
        if (Auth::user()->classification == "System Administrator") {
                return $next($request);
        }else{
             return redirect('/forbidden');
        }

        
    }
}
