<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\TokenTrait;

class Auth
{
    use TokenTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {        
        $bearerToken = request()->bearerToken();
        if($bearerToken == null){
            return response()->json([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Bearer token is required' 
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->get($bearerToken);
        
        //$token = TokenTrait::get( $bearerToken );
        if($token == false){
            return response()->json([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Invalid token' 
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        return $next($request);
    }
}
