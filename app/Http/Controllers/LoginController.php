<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Handle an authentication attempt.
     *
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function authenticate(Request $request): Response|JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            if(Auth::user()->role === 1) {
                return response()->json([
                    'token' => Auth::user()->createToken('auth_token', ['admin'])->plainTextToken,
                    'User' => Auth::user(),
                    'Client' => null,
                    'Staff' => Auth::user()->staff
                ]);
            }
            else {
                return response()->json([
                    'token' => Auth::user()->createToken('auth_token', ['client'])->plainTextToken,
                    'User' => Auth::user(),
                    'Client' => Auth::user()->client,
                    'Staff' => null
                ]);
            }
        }

        return response(['code' => 401, 'message' => 'Bad credentials'], 401);
    }
}
