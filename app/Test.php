<?php

namespace App;

use JWTAuth;


class Test 
{
	public static function api_login($username, $password)
	{
		// $JWTAuth = new JWTAuth;
		$token = JWTAuth::attempt([
			'email' => $username,
			'password' => $password,
		]);
		return $token;
	}

}
