<?php

namespace App\Models;

use App\Enums\HostAccountType;
use App\Enums\UserRole;
use App\Services\Email\EmailService;
use App\Support\PricingCurrency;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'host_account_type',
        'phone',
        'kennitala',
        'profile_photo_path',
        'locale',
        'currency',
        'integration_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'profile_photo_path',
        'integration_token',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'host_account_type' => HostAccountType::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isFullAdmin() || $this->isDesigner();
    }

    public function isFullAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isDesigner(): bool
    {
        return $this->role === UserRole::Designer;
    }

    public function canManageSiteContent(): bool
    {
        return $this->isFullAdmin();
    }

    public function canPreviewSite(): bool
    {
        return $this->isFullAdmin() || $this->isDesigner();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function ordersCreatedAsAdmin(): HasMany
    {
        return $this->hasMany(Order::class, 'created_by_admin_id');
    }

    public function guestHouseBookings(): HasMany
    {
        return $this->hasMany(GuestHouseBooking::class);
    }

    public function guestHouses(): HasMany
    {
        return $this->hasMany(GuestHouse::class);
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }

    public function isHost(): bool
    {
        return $this->role === UserRole::Host || $this->role === UserRole::Admin;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->profile_photo_path);
    }

    public function pricingCurrency(): string
    {
        return PricingCurrency::forUser($this);
    }

    public function ensureIntegrationToken(): string
    {
        if (! $this->integration_token) {
            $this->regenerateIntegrationToken();
        }

        return $this->integration_token;
    }

    public function regenerateIntegrationToken(): string
    {
        $token = Str::random(48);
        $this->forceFill(['integration_token' => $token])->save();

        return $token;
    }

    /**
     * Send the branded password reset email instead of Laravel's default markdown notification.
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $frontend = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $email = urlencode($this->getEmailForPasswordReset());
        $intent = $this->isHost() ? '&intent=host' : '';
        $resetUrl = "{$frontend}/reset-password?token={$token}&email={$email}{$intent}";

        app(EmailService::class)->send('password_reset', $this->email, [
            'customer_name' => $this->name,
            'reset_url' => $resetUrl,
            'expires' => (string) config('auth.passwords.users.expire', 60),
        ]);
    }
}
