<?php

namespace App\Models;

use App\Models\Role;
use App\Traits\HasRbac;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory;
    use HasRbac;
    use Notifiable;
    use SoftDeletes;


    public const MEMBER_STATUS_MEMBER = 'member';
    public const MEMBER_STATUS_APPRENTICE = 'apprentice';
    public const MEMBER_STATUS_LAWYER_ASSISTANT = 'lawyer_assistant';
    public const MEMBER_STATUS_INACTIVE = 'inactive';
    public const MEMBER_STATUS_SUSPENDED = 'suspended';
    public const MEMBER_STATUS_DECEASED = 'deceased';
    public const MEMBER_STATUS_OFFICE_STAFF = 'office_staff';

    public const MEMBER_TYPE_MOTHER = 'mother_member';
    public const MEMBER_TYPE_SUB = 'sub_member';

    public const CHAMBER_TYPE_STANDARD = 'standard';
    public const CHAMBER_TYPE_JOINT = 'joint';
    public const CHAMBER_TYPE_AC = 'ac';

    public const MEMBER_STATUSES = [
        self::MEMBER_STATUS_MEMBER,
        self::MEMBER_STATUS_APPRENTICE,
        self::MEMBER_STATUS_LAWYER_ASSISTANT,
        self::MEMBER_STATUS_INACTIVE,
        self::MEMBER_STATUS_SUSPENDED,
        self::MEMBER_STATUS_DECEASED,
        self::MEMBER_STATUS_OFFICE_STAFF,
    ];

    public const MEMBER_TYPES = [
        self::MEMBER_TYPE_MOTHER,
        self::MEMBER_TYPE_SUB,
    ];

    public const CHAMBER_TYPES = [
        self::CHAMBER_TYPE_STANDARD,
        self::CHAMBER_TYPE_JOINT,
        self::CHAMBER_TYPE_AC,
    ];

    protected $fillable = [
        'full_name',
        'email',
        'mobile',
        'password',
        'father_name',
        'mother_name',
        'dob',
        'gender',
        'blood_group',
        'present_address',
        'permanent_address',
        'nid_passport_number',
        'nid_images',
        'photo',
        'membership_number',
        'bar_join_date',
        'sanad_number',
        'sanad_date',
        'lawyer_id',
        'approval_status',
        'member_status',
        'member_type',
        'chamber_room_no',
        'chamber_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'dob' => 'date',
            'bar_join_date' => 'date',
            'sanad_date' => 'date',
            'nid_images' => 'array',
            'two_factor_enabled' => 'boolean',
            'two_factor_phone_verified_at' => 'datetime',
            'two_factor_authenticator_secret' => 'encrypted',
            'two_factor_authenticator_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'model_has_roles',
            'user_id',
            'role_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeMembers($query)
    {
        return $query->where(
            'member_status',
            self::MEMBER_STATUS_MEMBER
        );
    }

    public function scopeMotherMembers($query)
    {
        return $query->members()
            ->where('member_type', self::MEMBER_TYPE_MOTHER);
    }

    public function scopeSubMembers($query)
    {
        return $query->members()
            ->where('member_type', self::MEMBER_TYPE_SUB);
    }

    public function scopeApprentices($query)
    {
        return $query->where(
            'member_status',
            self::MEMBER_STATUS_APPRENTICE
        );
    }

    public function scopeLawyerAssistants($query)
    {
        return $query->where(
            'member_status',
            self::MEMBER_STATUS_LAWYER_ASSISTANT
        );
    }

    public function routeNotificationForMail(
        ?object $notification = null
    ): ?string {
        return $this->email;
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    public function assistants(): HasMany
    {
        return $this->hasMany(User::class, 'lawyer_id');
    }

    public function scopeForLawyer(
        Builder $query,
        int $lawyerId
    ): Builder {
        return $query
            ->where('member_status', 'lawyer_assistant')
            ->where('lawyer_id', $lawyerId);
    }

    public function isLawyerAssistant(): bool
    {
        return $this->member_status === 'lawyer_assistant';
    }

    public function hasAssistants(): bool
    {
        return $this->assistants()->exists();
    }

    public function isMember(): bool
    {
        return $this->member_status === self::MEMBER_STATUS_MEMBER;
    }

    public function isMotherMember(): bool
    {
        return $this->isMember()
            && $this->member_type === self::MEMBER_TYPE_MOTHER;
    }

    public function isSubMember(): bool
    {
        return $this->isMember()
            && $this->member_type === self::MEMBER_TYPE_SUB;
    }

    public function isApprentice(): bool
    {
        return $this->member_status === self::MEMBER_STATUS_APPRENTICE;
    }

    public function hasChamberAllocation(): bool
    {
        return filled($this->chamber_room_no)
            && filled($this->chamber_type);
    }

    public function getChamberTypeLabelAttribute(): ?string
    {
        return match ($this->chamber_type) {
            self::CHAMBER_TYPE_STANDARD => 'Standard Chamber',
            self::CHAMBER_TYPE_JOINT => 'Joint Chamber',
            self::CHAMBER_TYPE_AC => 'AC Chamber',
            default => null,
        };
    }

    public function getMemberTypeLabelAttribute(): ?string
    {
        return match ($this->member_type) {
            self::MEMBER_TYPE_MOTHER => 'Mother Member',
            self::MEMBER_TYPE_SUB => 'Sub Member',
            default => null,
        };
    }

    public function hasTwoFactorPhone(): bool
    {
        return filled($this->two_factor_phone)
            && $this->two_factor_phone_verified_at !== null;
    }

    public function hasTwoFactorAuthenticator(): bool
    {
        return filled($this->two_factor_authenticator_secret)
            && $this->two_factor_authenticator_confirmed_at !== null;
    }

    public function hasConfiguredTwoFactorMethod(): bool
    {
        return $this->hasTwoFactorPhone()
            || $this->hasTwoFactorAuthenticator();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function verifiedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'verified_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (blank($user->ppt)) {
                do {
                    $token = Str::random(64);
                } while (
                    static::query()->where('ppt', $token)->exists()
                );

                $user->ppt = $token;
            }
        });
    }

    public function publicCardUrl(): string
    {
        return route('member.card', ['ppt' => $this->ppt]);
    }

    public function qrSvg(int $size = 180): string
    {
        return QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->errorCorrection('M')
            ->generate($this->publicCardUrl());
    }
}
