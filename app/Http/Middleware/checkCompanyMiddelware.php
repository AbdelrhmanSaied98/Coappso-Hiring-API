<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class checkCompanyMiddelware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $company = auth('company')->userOrFail();
            if($company->ban_times != 0)
            {
                return $this->returnError(400, 'you have been banned for '.$company->ban_times .' days');
            }
            if($company->isBlocked == 1)
            {
                return $this->returnError(400, 'you have been blocked');
            }
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return $next($request);
        }
        return $next($request);
    }

    public function returnError($errNum, $msg)
    {
        return response([
            'status' => false,
            'code' => $errNum,
            'msg' => $msg
        ], $errNum)
            ->header('Content-Type', 'text/json');
    }

}
