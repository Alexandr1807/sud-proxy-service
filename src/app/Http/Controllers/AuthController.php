<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/register
     *
     * Тело запроса (JSON или x-www-form-urlencoded):
     *  - name      : string, обязательно
     *  - email     : string, обязательно, уникально
     *  - password  : string, обязательно, минимум 6 символов
     *  - password_confirmation: string, обязательно, совпадает с password
     *
     * Возвращает JSON с полями:
     * {
     *   "user": { ... данные пользователя ... },
     *   "access_token": "...",
     *   "token_type": "Bearer"
     * }
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $tokenResult = $user->createToken('Personal Access Token');
        $token       = $tokenResult->accessToken;

        return response()->json([
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 201);
    }

    /**
     * POST /api/login
     *
     * Тело запроса:
     *  - email    : string, обязательно
     *  - password : string, обязательно
     *
     * Если учётные данные верны, возвращаем JSON с:
     * {
     *   "user": { ... },
     *   "access_token": "...",
     *   "token_type": "Bearer"
     * }
     *
     * Если неверно — бросаем ValidationException с ошибкой "Неверные учётные данные."
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учётные данные.'],
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $tokenResult = $user->createToken('Personal Access Token');
        $token       = $tokenResult->accessToken;
        $expiresAt   = $tokenResult->token->expires_at;

        return response()->json([
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_at'    => $expiresAt->toDateTimeString(),
        ], 200);
    }

    /**
     * POST /api/logout
     *
     * Заголовок:
     *   Authorization: Bearer {access_token}
     *
     * Этот метод доступен только при прохождении middleware auth:api.
     * Отзывает текущий токен и возвращает 200 OK.
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Токен успешно отозван.'
        ], 200);
    }

    public function allUsers(Request $request)
    {
        $users = User::all();

        return response()->json($users, 200);
    }
}
