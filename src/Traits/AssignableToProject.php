<?php

namespace Whilesmart\Projects\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Whilesmart\Projects\Models\Project;
use Whilesmart\Projects\Models\ProjectAssignment;

trait AssignableToProject
{
    public function projectAssignments(): MorphMany
    {
        $assignmentModel = config('projects.models.project_assignment', ProjectAssignment::class);

        return $this->morphMany($assignmentModel, 'assignable');
    }

    public function assignedProjects(?string $role = null)
    {
        $projectModel = config('projects.models.project', Project::class);

        $assignmentIds = $this->projectAssignments()
            ->when($role, fn ($q) => $q->where('role', $role))
            ->pluck('project_id');

        return $projectModel::whereIn('id', $assignmentIds);
    }

    public function isAssignedToProject(Project $project, ?string $role = null): bool
    {
        return $project->isAssigned($this, $role);
    }
}
