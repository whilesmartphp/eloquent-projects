<?php

namespace Whilesmart\Projects\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Whilesmart\Projects\Models\Project;

trait BelongsToProject
{
    public function project(): BelongsTo
    {
        $projectModel = config('projects.models.project', Project::class);

        return $this->belongsTo($projectModel);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
