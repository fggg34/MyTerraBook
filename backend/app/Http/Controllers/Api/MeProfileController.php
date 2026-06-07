<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Me\UpdatePasswordRequest;
use App\Http\Requests\Me\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;

class MeProfileController extends Controller
{
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->safe()->only(['name', 'email', 'phone']));

        return response()->json([
            'message' => 'Profile updated.',
            'user' => $user->fresh(),
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return response()->json(['message' => 'Password updated.']);
    }
}
