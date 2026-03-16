<?php

namespace Whilesmart\Projects\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Whilesmart\Projects\Events\ProjectAssigned;
use Whilesmart\Projects\Events\ProjectUnassigned;
use Whilesmart\Projects\Models\Project;
use Whilesmart\Projects\Traits\AssignableToProject;

class ProjectAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function afterRefreshingDatabase(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function createProject(string $title = 'Test'): Project
    {
        return Project::create([
            'owner_type' => 'App\\Models\\Workspace',
            'owner_id' => 1,
            'title' => $title,
        ]);
    }

    public function test_assign_entity_to_project(): void
    {
        Event::fake([ProjectAssigned::class]);

        $project = $this->createProject();
        $agent = AssignableAgent::create(['name' => 'Bot']);

        $assignment = $project->assign($agent, 'generator');

        $this->assertTrue($project->isAssigned($agent, 'generator'));
        $this->assertCount(1, $project->assignments);
        Event::assertDispatched(ProjectAssigned::class);
    }

    public function test_assign_is_idempotent(): void
    {
        $project = $this->createProject();
        $agent = AssignableAgent::create(['name' => 'Bot']);

        $project->assign($agent, 'generator');
        $project->assign($agent, 'generator');

        $this->assertCount(1, $project->fresh()->assignments);
    }

    public function test_assign_same_entity_different_roles(): void
    {
        $project = $this->createProject();
        $agent = AssignableAgent::create(['name' => 'Bot']);

        $project->assign($agent, 'generator');
        $project->assign($agent, 'reviewer');

        $this->assertCount(2, $project->fresh()->assignments);
        $this->assertTrue($project->isAssigned($agent, 'generator'));
        $this->assertTrue($project->isAssigned($agent, 'reviewer'));
    }

    public function test_unassign_by_role(): void
    {
        Event::fake([ProjectUnassigned::class]);

        $project = $this->createProject();
        $agent = AssignableAgent::create(['name' => 'Bot']);

        $project->assign($agent, 'generator');
        $project->assign($agent, 'reviewer');
        $project->unassign($agent, 'generator');

        $this->assertFalse($project->isAssigned($agent, 'generator'));
        $this->assertTrue($project->isAssigned($agent, 'reviewer'));
        Event::assertDispatched(ProjectUnassigned::class);
    }

    public function test_unassign_all_roles(): void
    {
        $project = $this->createProject();
        $agent = AssignableAgent::create(['name' => 'Bot']);

        $project->assign($agent, 'generator');
        $project->assign($agent, 'reviewer');
        $project->unassign($agent);

        $this->assertFalse($project->isAssigned($agent));
    }

    public function test_assignees_filtered_by_role(): void
    {
        $project = $this->createProject();
        $a1 = AssignableAgent::create(['name' => 'Agent 1']);
        $a2 = AssignableAgent::create(['name' => 'Agent 2']);
        $team = AssignableTeam::create(['name' => 'Team']);

        $project->assign($a1, 'generator');
        $project->assign($a2, 'reviewer');
        $project->assign($team, 'stakeholder');

        $this->assertCount(1, $project->assignees('generator')->get());
        $this->assertCount(1, $project->assignees('reviewer')->get());
        $this->assertCount(3, $project->assignees()->get());
    }

    public function test_assignable_to_project_trait(): void
    {
        $project1 = $this->createProject('P1');
        $project2 = $this->createProject('P2');
        $agent = AssignableAgent::create(['name' => 'Bot']);

        $project1->assign($agent, 'generator');
        $project2->assign($agent, 'reviewer');

        $this->assertCount(2, $agent->projectAssignments);
        $this->assertCount(2, $agent->assignedProjects()->get());
        $this->assertCount(1, $agent->assignedProjects('generator')->get());
        $this->assertTrue($agent->isAssignedToProject($project1, 'generator'));
        $this->assertFalse($agent->isAssignedToProject($project1, 'reviewer'));
    }

    public function test_assignment_with_metadata(): void
    {
        $project = $this->createProject();
        $agent = AssignableAgent::create(['name' => 'Bot']);

        $assignment = $project->assign($agent, 'generator', null, [
            'permissions' => ['read', 'write'],
            'priority' => 'high',
        ]);

        $this->assertEquals(['read', 'write'], $assignment->metadata['permissions']);
        $this->assertEquals('high', $assignment->metadata['priority']);
    }

    public function test_assignment_with_assigned_by(): void
    {
        $project = $this->createProject();
        $agent = AssignableAgent::create(['name' => 'Bot']);
        $admin = AssignableAgent::create(['name' => 'Admin']);

        $assignment = $project->assign($agent, 'generator', $admin);

        $this->assertEquals(AssignableAgent::class, $assignment->assigned_by_type);
        $this->assertEquals($admin->id, $assignment->assigned_by_id);
    }
}

class AssignableAgent extends Model
{
    use AssignableToProject;

    protected $table = 'agents';

    protected $fillable = ['name'];
}

class AssignableTeam extends Model
{
    use AssignableToProject;

    protected $table = 'teams';

    protected $fillable = ['name'];
}
