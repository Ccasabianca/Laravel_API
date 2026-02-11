<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class UserController extends Controller
{

/**
 * @OA\Post(
 *     path="/api/v1/register",
 *     summary="Inscription utilisateur",
 *     description="Crée un utilisateur et lui accorde un token",
 *     tags={"Auth"},
 *     @OA\Parameter(name="Accept", in="header", required=true, @OA\Schema(type="string", example="application/json")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","email","password"},
 *             @OA\Property(property="name", type="string", example="Jean Dupont"),
 *             @OA\Property(property="email", type="string", example="jean@mail.com"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Utilisateur créé",
 *         @OA\JsonContent(example={"token":"...","user":{"id":1,"name":"Jean Dupont","email":"jean@mail.com"}})
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erreur validation",
 *         @OA\JsonContent(example={"message":"The email field is required.","errors":{"email":{"The email field is required."}}})
 *     )
 * )
 */


    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 201);
    }

/**
 * @OA\Post(
 *     path="/api/v1/login",
 *     summary="Connexion utilisateur",
 *     description="Authentifie un utilisateur. Limité à 10 tentatives par minute",
 *     tags={"Auth"},
 *     @OA\Parameter(name="Accept", in="header", required=true, @OA\Schema(type="string", example="application/json")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","password"},
 *             @OA\Property(property="email", type="string", example="jean@mail.com"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Connexion réussie",
 *         @OA\JsonContent(example={"token":"...","user":{"id":1,"name":"Jean Dupont","email":"jean@mail.com"}})
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Identifiants invalides",
 *         @OA\JsonContent(example={"message":"Identifiants invalides"})
 *     )
 * )
 */


    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Identifiants invalides',
            ], 422);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

/**
 * @OA\Post(
 *     path="/api/v1/logout",
 *     summary="Déconnexion",
 *     description="Supprime le token actuel. Il faut être connecté",
 *     tags={"Auth"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="Accept", in="header", required=true, @OA\Schema(type="string", example="application/json")),
 *     @OA\Parameter(
 *         name="Authorization",
 *         in="header",
 *         required=true,
 *         description="Bearer {token}",
 *         @OA\Schema(type="string", example="Bearer eyJ0eXAiOiJKV1QiLCJhbGciOi...")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Déconnecté",
 *         @OA\JsonContent(example={"message":"Déconnecté"})
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *         @OA\JsonContent(example={"message":"Unauthenticated."})
 *     )
 * )
 */


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnecté',
        ]);
    }
}
