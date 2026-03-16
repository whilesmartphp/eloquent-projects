<?php

namespace Whilesmart\Projects\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Whilesmart\Projects\Events\ProjectArchived;
use Whilesmart\Projects\Events\ProjectAssigned;
use Whilesmart\Projects\Events\ProjectCreated;
use Whilesmart\Projects\Events\ProjectDeleted;
use Whilesmart\Projects\Events\ProjectUnarchived;
use Whilesmart\Projects\Events\ProjectUnassigned;
use Whilesmart\Projects\Events\ProjectUpdated;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_type',
        'owner_id',
        'creator_type',
        'creator_id',
        'status_id',
        'title',
        'description',
        'thumbnail_url',
        'metadata',
        'archived_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'archived_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'created' => ProjectCreated::class,
        'updated' => ProjectUpdated::class,
        'deleted' => ProjectDeleted::class,
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    public function status(): BelongsTo
    {
        $statusModel = config('projects.models.project_status', ProjectStatus::class);

        return $this->belongsTo($statusModel, 'status_id');
    }

    public function assignments(): HasMany
    {
        $assignmentModel = config('projects.models.project_assignment', ProjectAssignment::class);

        return $this->hasMany($assignmentModel);
    }

    public function assignees(?string $role = null): HasMany
    {
        $query = $this->assignments();

        if ($role) {
            $query->where('role', $role);
        }

        return $query;
    }

    public function assign(Model $assignable, string $role, ?Model $assignedBy = null, array $metadata = []): ProjectAssignment
    {
        $assignment = $this->assignments()->firstOrCreate([
            'assignable_type' => get_class($assignable),
            'assignable_id' => $assignable->getKey(),
            'role' => $role,
        ], [
            'assigned_by_type' => $assignedBy ? get_class($assignedBy) : null,
            'assigned_by_id' => $assignedBy?->getKey(),
            'metadata' => $metadata,
        ]);

        if ($assignment->wasRecentlyCreated) {
            ProjectAssigned::dispatch($this, $assignment);
        }

        return $assignment;
    }

    public function unassign(Model $assignable, ?string $role = null): void
    {
        $query = $this->assignments()
            ->where('assignable_type', get_class($assignable))
            ->where('assignable_id', $assignable->getKey());

        if ($role) {
            $query->where('role', $role);
        }

        $query->delete();

        ProjectUnassigned::dispatch($this, $assignable, $role);
    }

    public function isAssigned(Model $assignable, ?string $role = null): bool
    {
        $query = $this->assignments()
            ->where('assignable_type', get_class($assignable))
            ->where('assignable_id', $assignable->getKey());

        if ($role) {
            $query->where('role', $role);
        }

        return $query->exists();
    }

    public function scopeOwnedBy($query, Model $owner)
    {
        return $query->where('owner_type', get_class($owner))
            ->where('owner_id', $owner->getKey());
    }

    public function scopeCreatedBy($query, Model $creator)
    {
        return $query->where('creator_type', get_class($creator))
            ->where('creator_id', $creator->getKey());
    }

    public function scopeWithStatus($query, string $slug)
    {
        return $query->whereHas('status', fn ($q) => $q->where('slug', $slug));
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function archive(): void
    {
        $this->update(['archived_at' => now()]);
        ProjectArchived::dispatch($this);
    }

    public function unarchive(): void
    {
        $this->update(['archived_at' => null]);
        ProjectUnarchived::dispatch($this);
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function getMetadata(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->metadata;
        }

        return data_get($this->metadata, $key, $default);
    }

    public function setMetadata($keyOrArray, $value = null): void
    {
        if (is_array($keyOrArray)) {
            $this->metadata = $keyOrArray;
        } else {
            $metadata = $this->metadata ?? [];
            data_set($metadata, $keyOrArray, $value);
            $this->metadata = $metadata;
        }
    }
}
