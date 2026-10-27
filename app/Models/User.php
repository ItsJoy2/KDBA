<?php

namespace App\Models;

use App\Models\Role;
use App\Traits\HasRbac;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use HasRbac;
    use Notifiable;
    use SoftDeletes;

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
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Roles assigned to this user.
     */
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
        return $this->belongsTo(
            self::class,
            'created_by'
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'updated_by'
        );
    }

    /**
     * Scope for active membership records.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Route notifications to the user's email address.
     */
    public function routeNotificationForMail(
        ?object $notification = null
    ): ?string {
        return $this->email;
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'lawyer_id'
        );
    }
    public function assistants(): HasMany
    {
        return $this->hasMany(
            User::class,
            'lawyer_id'
        );
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

}