<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Me\UpdateCompanyLogoRequest;
use App\Http\Requests\Me\UpdatePasswordRequest;
use App\Http\Requests\Me\UpdateProfilePhotoRequest;
use App\Http\Requests\Me\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MeProfileController extends Controller
{
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $fields = ['name', 'email', 'phone'];
        if ($user->isHost()) {
            $fields[] = 'currency';
            $fields[] = 'kennitala';
        }
        $user->update($request->safe()->only($fields));

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

    public function updatePhoto(UpdateProfilePhotoRequest $request): JsonResponse
    {
        $user = $request->user();
        $previousPath = $user->profile_photo_path;

        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->update(['profile_photo_path' => $path]);

        if ($previousPath && Storage::disk('public')->exists($previousPath)) {
            Storage::disk('public')->delete($previousPath);
        }

        return response()->json([
            'message' => 'Profile photo updated.',
            'user' => $user->fresh(),
        ]);
    }

    public function deletePhoto(): JsonResponse
    {
        $user = auth()->user();
        $previousPath = $user->profile_photo_path;

        if ($previousPath) {
            if (Storage::disk('public')->exists($previousPath)) {
                Storage::disk('public')->delete($previousPath);
            }
            $user->update(['profile_photo_path' => null]);
        }

        return response()->json([
            'message' => 'Profile photo removed.',
            'user' => $user->fresh(),
        ]);
    }

    public function updateLogo(UpdateCompanyLogoRequest $request): JsonResponse
    {
        $user = $request->user();
        $previousPath = $user->company_logo_path;

        $path = $request->file('logo')->store('company-logos', 'public');
        $user->update(['company_logo_path' => $path]);

        if ($previousPath && Storage::disk('public')->exists($previousPath)) {
            Storage::disk('public')->delete($previousPath);
        }

        return response()->json([
            'message' => 'Company logo updated.',
            'user' => $user->fresh(),
        ]);
    }

    public function deleteLogo(): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user->isHost(), 403);

        $previousPath = $user->company_logo_path;

        if ($previousPath) {
            if (Storage::disk('public')->exists($previousPath)) {
                Storage::disk('public')->delete($previousPath);
            }
            $user->update(['company_logo_path' => null]);
        }

        return response()->json([
            'message' => 'Company logo removed.',
            'user' => $user->fresh(),
        ]);
    }
}
