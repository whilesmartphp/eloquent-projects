<?php

namespace Whilesmart\Projects\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Whilesmart\Projects\Models\Project;

trait HasProjects
{
    public function projects(): MorphMany
    {
        $projectModel = config('projects.models.project', Project::class);

        return $this->morphMany($projectModel, 'owner');
    }

    public function createProject(
        string $title,
        ?Model $creator = null,
        ?string $description = null,
        array $metadata = [],
    ): Project {
        return $this->projects()->create([
            'title' => $title,
            'creator_type' => $creator ? get_class($creator) : null,
            'creator_id' => $creator?->getKey(),
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
