<?php

namespace Whilesmart\Projects\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProjectAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'assignable_type',
        'assignable_id',
        'role',
        'assigned_by_type',
        'assigned_by_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function project(): BelongsTo
    {
        $projectModel = config('projects.models.project', Project::class);

        return $this->belongsTo($projectModel);
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedBy(): MorphTo
    {
        return $this->morphTo('assigned_by');
    }

    public function scopeForRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeForAssignable($query, Model $assignable)
    {
        return $query->where('assignable_type', get_class($assignable))
            ->where('assignable_id', $assignable->getKey());
    }
}
