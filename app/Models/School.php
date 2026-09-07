<?php

namespace App\Models;

use App\Enums\SchoolRole;
use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $logo_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $logo_url
 */
#[Fillable(['name', 'description', 'logo_path'])]
class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $appends = ['logo_url'];

    /**
     * Full URL for the school logo served from private storage.
     *
     * @return Attribute<string|null, never>
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->logo_path ? Storage::temporaryUrl($this->logo_path, now()->addMinutes(30)) : null);
    }

    /**
     * Get the users belonging to this school.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    /**
     * Get the admin users of this school.
     *
     * @return BelongsToMany<User, $this>
     */
    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivot('role', SchoolRole::Admin);
    }

    /**
     * Determine whether the given user is a member (any role) of this school.
     */
    public function hasMember(User $user): bool
    {
        return $this->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the given user is an admin of this school.
     */
    public function hasAdmin(User $user): bool
    {
        return $this->admins()->where('users.id', $user->id)->exists();
    }

    /**
     * Get the pupils belonging to this school.
     *
     * @return HasMany<Pupil, $this>
     */
    public function pupils(): HasMany
    {
        return $this->hasMany(Pupil::class);
    }

    /**
     * Get the training sessions belonging to this school.
     *
     * @return HasMany<TrainingSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }
}
