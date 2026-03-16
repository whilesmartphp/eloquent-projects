<?php

namespace Whilesmart\Projects\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Whilesmart\Projects\Events\ProjectArchived;
use Whilesmart\Projects\Events\ProjectCreated;
use Whilesmart\Projects\Events\ProjectUnarchived;
use Whilesmart\Projects\Models\Project;
use Whilesmart\Projects\Models\ProjectStatus;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected function afterRefreshingDatabase(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function test_can_create_project(): void
    {
        $project = Project::create([
            'owner_type' => FakeWorkspace::class,
            'owner_id' => 1,
            'title' => 'My Project',
        ]);

        $this->assertDatabaseHas('projects', [
            'title' => 'My Project',
            'owner_type' => FakeWorkspace::class,
            'owner_id' => 1,
        ]);
    }

    public function test_project_fires_created_event(): void
    {
        Event::fake([ProjectCreated::class]);

        Project::create([
            'owner_type' => FakeWorkspace::class,
            'owner_id' => 1,
            'title' => 'Event Test',
        ]);

        Event::assertDispatched(ProjectCreated::class);
    }

    public function test_project_with_creator(): void
    {
        $project = Project::create([
            'owner_type' => FakeWorkspace::class,
            'owner_id' => 1,
            'creator_type' => FakeUser::class,
            'creator_id' => 5,
            'title' => 'Created by user',
        ]);

        $this->assertEquals(FakeUser::class, $project->creator_type);
        $this->assertEquals(5, $project->creator_id);
    }

    public function test_project_with_status(): void
    {
        $status = ProjectStatus::create([
            'name' => 'Active',
            'slug' => 'active',
            'color' => '#00ff00',
        ]);

        $project = Project::create([
            'owner_type' => FakeWorkspace::class,
            'owner_id' => 1,
            'status_id' => $status->id,
            'title' => 'Status Test',
        ]);

        $this->assertEquals('Active', $project->status->name);
    }

    public function test_archive_and_unarchive(): void
    {
        Event::fake([ProjectArchived::class, ProjectUnarchived::class]);

        $project = Project::create([
            'owner_type' => FakeWorkspace::class,
            'owner_id' => 1,
            'title' => 'Archive Test',
        ]);

        $this->assertFalse($project->isArchived());

        $project->archive();
        $this->assertTrue($project->fresh()->isArchived());
        Event::assertDispatched(ProjectArchived::class);

        $project->unarchive();
        $this->assertFalse($project->fresh()->isArchived());
        Event::assertDispatched(ProjectUnarchived::class);
    }

    public function test_active_and_archived_scopes(): void
    {
        Project::create([
            'owner_type' => FakeWorkspace::class,
            'owner_id' => 1,
            'title' => 'Active',
        ]);

        $archived = Project::create([
            'owner_type' => FakeWorkspace::class,
            'owner_id' => 1,
            'title' => 'Archived',
        ]);
        $archived->archive();

        $this->assertCount(1, Project::active()->get());
        $this->assertCount(1, Project::archived()->get());
    }

    public function test_owned_by_scope(): void
    {
        $ws = FakeWorkspace::create(['name' => 'WS1']);

        Project::create([
            'owner_type' => FakeWorkspace::class,
            'owner_id' => $ws->id,
            'title' => 'Owned',
        ]);

        Project::create([
            'owner_type' => FakeWorkspace::class,
            'owner_id' => 999,
            'title' => 'Other',
        ]);

        $this->assertCount(1, Project::ownedBy($ws)->get());
    }

    public function test_with_status_scope(): void
    {
        $active = ProjectStatus::create(['name' => 'Active', 'slug' => 'active']);
        $done = ProjectStatus::create(['name' => 'Done', 'slug' => 'done']);

        Project::create([
            'owner_type' => FakeWorkspace::class,
            'owner_id' => 1,
            'status_id' => $active->id,
            'title' => 'A',
        ]);

        Project::create([
            'owner_type' => FakeWorkspace::class,
            'owner_id' => 1,
            'status_id' => $done->id,
            'title' => 'B',
        ]);

        $this->assertCount(1, Project::withStatus('active')->get());
        $this->assertCount(1, Project::withStatus('done')->get());
    }

    public function test_metadata(): void
    {
        $project = Project::create([
            'owner_type' => FakeWorkspace::class,
            'owner_id' => 1,
            'title' => 'Meta',
            'metadata' => ['key' => 'value'],
        ]);

        $this->assertEquals('value', $project->getMetadata('key'));
        $this->assertNull($project->getMetadata('missing'));
        $this->assertEquals('default', $project->getMetadata('missing', 'default'));

        $project->setMetadata('new_key', 'new_value');
        $project->save();
        $this->assertEquals('new_value', $project->fresh()->getMetadata('new_key'));
    }
}

class FakeWorkspace extends Model
{
    protected $table = 'workspaces';

    protected $fillable = ['name'];
}

class FakeUser extends Model
{
    protected $table = 'users';

    protected $fillable = ['name'];
}
