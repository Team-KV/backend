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
                return $this->sendData(
                    [
                        'Token' => Auth::user()->createToken('auth_token', ['admin'])->plainTextToken
                    ]
                );
            }
            else {
                return $this->sendData(
                    [
                        'Token' => Auth::user()->createToken('auth_token', ['client'])->plainTextToken
                    ]
                );
            }
        }

        return $this->sendUnauthorized('messages.badCredentials');
    }

    /**
     * Returns info about logged user
     *
     * @return JsonResponse
     */
    public function info(): JsonResponse
    {
        return $this->sendData(
            [
                'User' => User::getUserByID(Auth::id())
            ]
        );
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

        return $this->sendNoContent();
    }
}
