<?php

namespace Whilesmart\Projects\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Whilesmart\Projects\Models\Project;
use Whilesmart\Projects\Models\ProjectAssignment;

class ProjectAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Project $project,
        public ProjectAssignment $assignment,
    ) {}
}
