<?php

namespace Whilesmart\Projects\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Whilesmart\Projects\Models\Project;

interface HasProjects
{
    public function projects(): MorphMany;

    public function createProject(
        string $title,
        ?Model $creator = null,
        ?string $description = null,
        array $metadata = [],
    ): Project;
}
