<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
                Log::channel('reception')->info('Login admin user.', ['user_id' => Auth::user()->id]);
                return $this->sendData(
                    [
                        'Token' => Auth::user()->createToken('auth_token', ['admin'])->plainTextToken
                    ]
                );
            }
            else {
                Log::channel('reception')->info('Login client user.', ['user_id' => Auth::user()->id]);
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
        Log::channel('reception')->info('Logout user.', ['user_id' => Auth::user()->id]);
        Auth::user()->tokens->each(function($token) {
            $token->delete();
        });

        return $this->sendNoContent();
    }

    public function update($id, Request $request): Response|JsonResponse
    {
        $user = User::getUserByID($id);
        if($user == null) {
            return $this->sendNotFound('messages.userDoesntExistsError');
        }

        $params = $request->validate([
            'email' => ['string', 'nullable'],
            'password' => ['string', 'nullable'],
            'password_again' => ['string', 'nullable']
        ]);

        $userParams = [];
        if($params['email'] != null) {
            $userParams['email'] = $params['email'];
        }
        if($params['password'] != null) {
            if($params['password'] == $params['password_again']) {
                $userParams['password'] = Hash::make($params['email']);
            }
            else {
                return $this->sendConflict('messages.passwordError');
            }
        }

        try {
            $user->update($userParams);
            Log::channel('reception')->info('Updated user login credentials.', ['user_id' => Auth::user()->id, 'Params' => $userParams]);
        } catch (QueryException) {
            return $this->sendInternalError('messages.userUpdateError');
        }

        return $this->sendData(['User' => $user]);
    }
}
