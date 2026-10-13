<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // === LOGIN ===
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Username atau password salah'], 401);
        }

        // Hapus token lama
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id'              => $user->id,
                'username'        => $user->username,
                'nama'            => $user->nama,
                'role'            => $user->role,
                'saldo'           => $user->saldo,
                'poin_sampah'     => $user->poin_sampah,
                'poin_lingkungan' => $user->poin_lingkungan,
                'total_poin'      => $user->total_poin,
            ]
        ]);
    }

    // === LOGOUT ===
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => 'success', 'message' => 'Logout berhasil']);
    }

    // === INFO USER LOGIN ===
    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => [
                'id'              => $user->id,
                'username'        => $user->username,
                'nama'            => $user->nama,
                'role'            => $user->role,
                'saldo'           => $user->saldo,
                'poin_sampah'     => $user->poin_sampah,
                'poin_lingkungan' => $user->poin_lingkungan,
                'total_poin'      => $user->total_poin,
            ]
        ]);
    }
}
