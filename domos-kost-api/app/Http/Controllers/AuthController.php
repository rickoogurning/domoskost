<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by username or email
        $user = User::where('username', $request->username)
                   ->orWhere('email', $request->username)
                   ->with('roleModel')
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.'
            ], 401);
        }

        // Update last login
        $user->update(['last_login' => now()]);

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Load relationships for response
        $user->load(['roleModel', 'penghuni.kamar']);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'nama_lengkap' => $user->nama_lengkap,
                    'no_telp' => $user->no_telp,
                    'role' => $user->role,
                    'foto_profil' => $user->foto_profil,
                    'last_login' => $user->last_login,
                    'kamar' => $user->penghuni?->kamar ? [
                        'id' => $user->penghuni->kamar->id,
                        'kode_kamar' => $user->penghuni->kamar->kode_kamar,
                        'lantai' => $user->penghuni->kamar->lantai,
                        'tipe_kamar' => $user->penghuni->kamar->tipe_kamar,
                        'tarif_bulanan' => $user->penghuni->kamar->tarif_bulanan,
                    ] : null,
                ],
                'token' => $token
            ]
        ]);
    }

    /**
     * Logout user and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout gagal'
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roleModel', 'penghuni.kamar']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'nama_lengkap' => $user->nama_lengkap,
                'no_telp' => $user->no_telp,
                'alamat' => $user->alamat,
                'role' => $user->role,
                'foto_profil' => $user->foto_profil,
                'is_active' => $user->is_active,
                'last_login' => $user->last_login,
                'kamar' => $user->penghuni?->kamar ? [
                    'id' => $user->penghuni->kamar->id,
                    'kode_kamar' => $user->penghuni->kamar->kode_kamar,
                    'lantai' => $user->penghuni->kamar->lantai,
                    'tipe_kamar' => $user->penghuni->kamar->tipe_kamar,
                    'tarif_bulanan' => $user->penghuni->kamar->tarif_bulanan,
                    'fasilitas' => $user->penghuni->kamar->fasilitas,
                ] : null,
                'penghuni' => $user->penghuni ? [
                    'id' => $user->penghuni->id,
                    'no_ktp' => $user->penghuni->no_ktp,
                    'tempat_lahir' => $user->penghuni->tempat_lahir,
                    'tanggal_lahir' => $user->penghuni->tanggal_lahir,
                    'jenis_kelamin' => $user->penghuni->jenis_kelamin,
                    'pekerjaan' => $user->penghuni->pekerjaan,
                    'tanggal_masuk' => $user->penghuni->tanggal_masuk,
                    'status_penghuni' => $user->penghuni->status_penghuni,
                    'umur' => $user->penghuni->umur,
                    'lama_tinggal' => $user->penghuni->lama_tinggal,
                ] : null,
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'no_telp' => 'sometimes|string|max:20',
            'alamat' => 'sometimes|string',
            'foto_profil' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'nama_lengkap', 'email', 'no_telp', 'alamat', 'foto_profil'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => $user->fresh()
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil di-refresh',
            'data' => [
                'token' => $token
            ]
        ]);
    }
}
