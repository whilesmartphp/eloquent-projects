<?php

namespace Whilesmart\Projects\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Whilesmart\Projects\Models\Project;

class ProjectUnassigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Project $project,
        public Model $assignable,
        public ?string $role = null,
    ) {}
}
