<?php

namespace Whilesmart\Projects\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Whilesmart\Projects\Models\Project;
use Whilesmart\Projects\Traits\BelongsToProject;
use Whilesmart\Projects\Traits\HasProjects;

class HasProjectsTest extends TestCase
{
    use RefreshDatabase;

    protected function afterRefreshingDatabase(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->timestamps();
        });
    }

    public function test_has_projects_trait(): void
    {
        $org = ProjectOwnerOrg::create(['name' => 'Whilesmart']);

        $project = $org->createProject('Canvas');

        $this->assertCount(1, $org->projects);
        $this->assertEquals('Canvas', $project->title);
        $this->assertEquals(ProjectOwnerOrg::class, $project->owner_type);
        $this->assertEquals($org->id, $project->owner_id);
    }

    public function test_create_project_with_creator(): void
    {
        $org = ProjectOwnerOrg::create(['name' => 'Whilesmart']);
        $creator = ProjectOwnerOrg::create(['name' => 'Agent']);

        $project = $org->createProject('Canvas', $creator);

        $this->assertEquals(ProjectOwnerOrg::class, $project->creator_type);
        $this->assertEquals($creator->id, $project->creator_id);
    }

    public function test_belongs_to_project_trait(): void
    {
        $project = Project::create([
            'owner_type' => ProjectOwnerOrg::class,
            'owner_id' => 1,
            'title' => 'Test',
        ]);

        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Do stuff',
        ]);

        $this->assertEquals($project->id, $task->project->id);
    }

    public function test_for_project_scope(): void
    {
        $p1 = Project::create([
            'owner_type' => ProjectOwnerOrg::class,
            'owner_id' => 1,
            'title' => 'P1',
        ]);

        $p2 = Project::create([
            'owner_type' => ProjectOwnerOrg::class,
            'owner_id' => 1,
            'title' => 'P2',
        ]);

        ProjectTask::create(['project_id' => $p1->id, 'title' => 'Task A']);
        ProjectTask::create(['project_id' => $p1->id, 'title' => 'Task B']);
        ProjectTask::create(['project_id' => $p2->id, 'title' => 'Task C']);

        $this->assertCount(2, ProjectTask::forProject($p1->id)->get());
        $this->assertCount(1, ProjectTask::forProject($p2->id)->get());
    }
}

class ProjectOwnerOrg extends Model
{
    use HasProjects;

    protected $table = 'organizations';

    protected $fillable = ['name'];
}

class ProjectTask extends Model
{
    use BelongsToProject;

    protected $table = 'tasks';

    protected $fillable = ['project_id', 'title'];
}
