<?php

namespace Modules\User\Models;

use App\Contracts\MailSenderInterface;
use App\Mail\MailMessage;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Modules\Core\Traits\HasUlid;
use Modules\Notification\Models\Notification;
use Modules\User\Database\Factories\UserFactory;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, HasUlid, InteractsWithMedia, LogsActivity, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected static $recordEvents = ['created', 'updated', 'deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verification_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function getEmailForVerification(): string
    {
        return $this->email . '|' . ($this->email_verification_token ?? '');
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->withoutTimestamps(function () {
            $this->update(['email_verification_token' => Str::uuid()->toString()]);
        });

        parent::sendEmailVerificationNotification();
    }

    public function sendPasswordResetNotification($token): void
    {
        app(MailSenderInterface::class)->queue(
            MailMessage::make(
                to: $this->email,
                subject: 'Reset Your Password',
                view: 'auth::emails.password-reset',
                data: [
                    'user'     => $this,
                    'resetUrl' => url(route('password.reset', [
                        'token' => $token,
                        'email' => $this->email,
                    ], false)),
                ],
            )
        );
    }

}
