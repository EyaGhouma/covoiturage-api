<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // Méthode d'enregistrement
    public function register(Request $request)
    {
        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'address' => 'string',
            'phoneNumber' => 'string|max:20',
            'password' => 'required|string|min:8',
            'rate' => 'numeric',
            'isActive' => 'boolean',
            'isDrivingLicenseVerified'=> 'boolean',
            'gender'=> 'string'
        ]);

        $user = User::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'address' => $request->address,
            'phoneNumber' => $request->phoneNumber,
            'rate' => $request->rate,
            'isActive' => $request->isActive,
            'isDrivingLicenseVerified' => $request->isDrivingLicenseVerified,
            'password' => Hash::make($request->password),
            'rateCount' => 0,
            'gender' => $request->gender
        ]);
        return response()->json(['message' => 'Utilisateur enregistré avec succès !'], 201);
    }

    // Méthode de connexion
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Informations d’identification non valides'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['message' => 'Connexion réussie!', 'token' => $token, 'user' => $user]);
    }

    // Méthode de déconnexion
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Fermeture de session réussie!']);
    }

    public function verifyDrivingLicense($id)
    {
        $user = User::find($id);
        $user->isDrivingLicenseVerified = true;
        $user->save();
        return response()->json(['message' => 'Permis vérifié avec succès', 'user' => $user]);
    }

    public function getUserWithComments($userId)
{
    $user = User::with(['noticesAsDriver.passenger'])->find($userId);

    if (!$user) {
        return response()->json(['error' => 'Utilisateur non trouvé'], 404);
    }

    return response()->json([
        'id' => $user->id,
        'firstName' => $user->firstName,
        'lastName' =>  $user->lastName,
        'email' => $user->email,
        'phoneNumber' => $user->phoneNumber,
        'rate' => $user->rate,
        'rateCount' => $user->rateCount,
        'isDrivingLicenseVerified' => $user->isDrivingLicenseVerified,
        'gender'=>$user->gender,
        'comments' => $user->noticesAsDriver->map(function ($notice) {
            return [
                'comment' => $notice->comment,
                'rate' => $notice->rate,
                'from' => $notice->passenger ? ($notice->passenger->firstName . ' ' . $notice->passenger->lastName) : null,
                'date' => $notice->created_at->toDateString(),
            ];
        }),
    ]);
}
}

