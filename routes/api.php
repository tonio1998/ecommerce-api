<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

});

Route::post('/login', function (Request $request) {
    try {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->student && !$user->employee) {
            return response()->json([
                'message' => 'User has no associated role/profile'
            ], 403);
        }

        return response()->json([
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'EncryptedUserID' => $user->id
        ], 200);

    } catch (\Throwable$e) {
        return response()->json([
            'message' => 'An error occurred while processing your request',
            'error' => config('app.debug') ? $e->getMessage() : 'Server Error',
        ], 500);
    }
});

Route::post('/auth/google', function (Request $request) {
    $request->validate([
        'token' => 'required|string',
        'name' => 'required|string',
        'email' => 'required|string',
        'photo' => 'nullable|string',
    ]);

    $http = Http::timeout(30);

    if (!app()->environment('production')) {
        $http = $http->withoutVerifying();
    }

    $googleResponse = $http->get('https://oauth2.googleapis.com/tokeninfo', [
        'id_token' => $request->token,
    ]);

    if (!$googleResponse->ok()) {
        return response()->json(['message' => 'Invalid Google ID token'], 401);
    }

    $googleUser = $googleResponse->json();

    $email = $googleUser['email'] ?? null;
    $name = $googleUser['name'] ?? $email ?? 'Google User';

    if (!$email) {
        return response()->json(['message' => 'Email not found in token'], 400);
    }

    $user = User::where('email', $email)->first();
    if ($user) {
        $user->name = $name;
        $user->avatar = $request->input('photo');
        $user->save();
    }

    return response()->json([
        'token' => $user->createToken('mobile')->plainTextToken,
        'user' => $user,
        'roles' => $user->getRoleNames(),
        'permissions' => $user->getAllPermissions()->pluck('name'),
        'EncryptedUserID' => $user->id,
    ]);
});
