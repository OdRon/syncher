<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;

use App\User;

use Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tymon\JWTAuth\Contracts\Providers\JWT;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class ManualAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $a = $request->headers('Authorization');

        if($a){
            $t = explode('{', $a);
            $token = substr($t[1], 0, -1) || null;
            $tt = new \Tymon\JWTAuth\Token($token);
            try {
                $p = JWTAuth::decode($tt);
                $user = User::find($p->get('sub'));
                if(time() < $p->get('exp') && $user){}
                else{
                   throw new JWTException('The token has expired'); 
                }
                
            } catch (Exception $e) {
                throw new TokenInvalidException('Token Signature could not be verified.');                
            }
            



        }



        return $next($request);
    }
}
