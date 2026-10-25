<?php

namespace App\Models;

use App\Enums\HostAccountType;
use App\Enums\UserRole;
use App\Services\Email\EmailService;
use App\Support\PricingCurrency;
use App\Support\RichText;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'country_code',
        'kennitala',
        'profile_photo_path',
        'company_logo_path',
        'locale',
        'currency',
        'integration_token',
        'partner_provider',
        'partner_api_base_url',
        'partner_api_key',
        'partner_api_username',
        'partner_api_password',
        'partner_rental_id',
        'partner_api_enabled',
        'partner_commission_rate',
        'rental_terms_html',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'profile_photo_path',
        'company_logo_path',
        'integration_token',
        'partner_api_key',
        'partner_api_username',
        'partner_api_password',
    ];

    protected $appends = [
        'profile_photo_url',
        'company_logo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'host_account_type' => HostAccountType::class,
            'partner_api_key' => 'encrypted',
            'partner_api_username' => 'encrypted',
            'partner_api_password' => 'encrypted',
            'partner_rental_id' => 'integer',
            'partner_api_enabled' => 'boolean',
            'partner_commission_rate' => 'decimal:2',
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

    public function getCompanyLogoUrlAttribute(): ?string
    {
        if (! $this->company_logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->company_logo_path);
    }

    protected function rentalTermsHtml(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): ?string => RichText::clean(is_string($value) ? $value : null),
        );
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
