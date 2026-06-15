<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public const ROLE_ADMIN = 'admin';

    public const ROLE_USER = 'user';

    public const PROFILE_ENTREPRENEUR_BATIMENT = 'entrepreneur_batiment';

    public const PROFILE_ENTREPRISE_FOURNISSEUR = 'entreprise_fournisseur';

    public const PROFILE_ARTISAN = 'artisan';

    public const PROFILE_PARTICULIER = 'particulier';

    public const VALIDATION_PENDING = 'pending';

    public const VALIDATION_APPROVED = 'approved';

    public const VALIDATION_REJECTED = 'rejected';

    public const VALIDATION_CHANGES_REQUESTED = 'changes_requested';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_type',
        'phone',
        'is_active',
        'company_name',
        'company_address',
        'city',
        'country',
        'commune',
        'bio',
        'contact_email',
        'company_description',
        'years_experience',
        'activity_type',
        'company_size',
        'manager_name',
        'manager_contact',
        'artisan_availability',
        'profile_completed_at',
        'avatar_path',
        'profile_validation_status',
        'profile_validation_note',
        'profile_validated_at',
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
            'is_active' => 'boolean',
            'profile_completed_at' => 'datetime',
            'profile_validated_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Nom affiché marketplace / catalogue (entreprise prioritaire sur le nom personnel).
     */
    public function marketplaceDisplayName(): string
    {
        $company = trim((string) ($this->company_name ?? ''));
        if ($company !== '') {
            return $company;
        }

        $name = trim((string) ($this->name ?? ''));

        return $name !== '' ? $name : 'Prestataire';
    }

    public function isBusinessProfile(): bool
    {
        return in_array($this->profile_type, [
            self::PROFILE_ENTREPRISE_FOURNISSEUR,
            self::PROFILE_ENTREPRENEUR_BATIMENT,
        ], true);
    }

    /**
     * Garde name et company_name alignés pour les comptes entreprise.
     */
    public function syncBusinessIdentity(): void
    {
        if (! $this->isBusinessProfile()) {
            return;
        }

        $company = trim((string) ($this->company_name ?? ''));
        $name = trim((string) ($this->name ?? ''));

        if ($company !== '') {
            $this->name = $company;

            return;
        }

        if ($name !== '') {
            $this->company_name = $name;
        }
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function devis()
    {
        return $this->hasMany(Devis::class);
    }

    public function besoins()
    {
        return $this->hasMany(Besoin::class);
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class, 'applicant_id');
    }

    public function messagesSent(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messagesReceived(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function devisAsClient()
    {
        return $this->hasMany(Devis::class, 'client_user_id');
    }

    public function artisanBusinessCard(): HasOne
    {
        return $this->hasOne(ArtisanBusinessCard::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(UserDocument::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function assignedSupportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to_user_id');
    }
}
