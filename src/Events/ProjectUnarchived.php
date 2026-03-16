<?php

namespace Whilesmart\Projects\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Whilesmart\Projects\Models\Project;

class ProjectUnarchived
{
    use Dispatchable, SerializesModels;

    public function __construct(public Project $project) {}
}
