<?php

namespace App\Http\Controllers;

use App\Models\User;
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
                    'token' => Auth::user()->createToken('auth_token', ['admin'])->plainTextToken
                ]);
            }
            else {
                return response()->json([
                    'token' => Auth::user()->createToken('auth_token', ['client'])->plainTextToken
                ]);
            }
        }

        return response(['code' => 401, 'message' => 'Bad credentials'], 401);
    }

    /**
     * Returns info about logged user
     *
     * @return JsonResponse
     */
    public function info(): JsonResponse
    {
        return response()->json([
            'User' => User::with('staff')->with('client')->find(Auth::id())
        ]);
    }

    /**
     * Logouts user
     *
     * @return Response
     */
    public function logout(): Response
    {
        Auth::user()->tokens->each(function($token) {
            $token->delete();
        });

        return response( '', 204);
    }
}
