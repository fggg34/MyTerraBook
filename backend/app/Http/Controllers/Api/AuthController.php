<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterHostRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\Email\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly EmailService $email,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create([
            ...$request->validated(),
            'role' => UserRole::Customer,
        ]);

        $this->email->send('customer_welcome', $user->email, [
            'customer_name' => $user->name,
        ]);

        $token = $user->createToken('customer-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    public function registerHost(RegisterHostRequest $request): JsonResponse
    {
        $user = User::query()->create([
            ...$request->validated(),
            'role' => UserRole::Host,
        ]);

        $this->email->send('host_welcome', $user->email, [
            'host_name' => $user->name,
        ]);

        $token = $user->createToken('host-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    public function applyAsHost(): JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->role === UserRole::Admin) {
            return response()->json(['message' => 'Admins already have full access.', 'user' => $user]);
        }

        if ($user->role === UserRole::Host) {
            return response()->json(['message' => 'You are already a host.', 'user' => $user]);
        }

        $user->update(['role' => UserRole::Host]);

        $this->email->send('host_welcome', $user->email, [
            'host_name' => $user->name,
        ]);

        return response()->json(['message' => 'Host account activated.', 'user' => $user->fresh()]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->first();
        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 422);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function logout(): JsonResponse
    {
        auth()->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'If an account exists for that email, we sent a password reset link.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password])->save();

                $this->email->send('password_changed', $user->email, [
                    'customer_name' => $user->name,
                ]);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['message' => 'Your password has been reset. You can sign in now.']);
    }
}
