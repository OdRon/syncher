<?php

namespace App\Api\V1\Controllers;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Auth;

/**
 * @Resource("Login")
 */
class LoginController extends Controller
{
    /**
     * Login as a user.
     *
     * Middleware Guest
     *
     * Use the token to authorise your other requests
     *
     * Pass the token in a header
     *
     * Authorization: bearer {token}
     *
     * @Post("/auth/login")
     * @Request({"email": "email", "password": "string"})
     * @Response(200, body={"status": "ok", "token": "token", "expires_in": "ttl in minutes"})
     *
     */
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['email', 'password']);

        try {
            // $token = Auth::guard()->attempt($credentials);
            $token = $JWTAuth->attempt($credentials);

            if(!$token) {
                throw new AccessDeniedHttpException();
            }

        } catch (JWTException $e) {
            throw new HttpException(500);
        }

        $u = \App\User::where('email', $request->input('email'))->first();
        if(!in_array($u->user_type_id, [10, 100])) throw new AccessDeniedHttpException();

        return response()
            ->json([
                'status' => 'ok',
                'token' => $token,
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                // 'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
            ]);
    }
}
