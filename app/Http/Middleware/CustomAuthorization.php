<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
use Closure;

class CustomAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  $operation
     * @return mixed
     */
    public function handle($request, Closure $next, $operation)
    {
        //check second validation of authentication
        //while a user is in active session and the status of hers is changed than logout her
        if(Auth::user()->status != 1){
            Auth::logout();
            return redirect("/login");
        }

        if(Helper::has_right(Auth::user()->operations,$operation)){
            return $next($request);
        }
        else{
            return abort(404);
        }
    }
}
