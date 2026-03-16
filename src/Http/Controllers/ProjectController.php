<?php

namespace Whilesmart\Projects\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Whilesmart\Projects\Enums\HookAction;
use Whilesmart\Projects\Models\Project;
use Whilesmart\Projects\Traits\HasMiddlewareHooks;

class ProjectController extends Controller
{
    use HasMiddlewareHooks;

    protected function projectModel(): string
    {
        return config('projects.models.project', Project::class);
    }

    public function index(Request $request): JsonResponse
    {
        $request = $this->runBeforeHooks($request, HookAction::INDEX);

        $query = $this->projectModel()::query();

        if ($request->has('owner_type') && $request->has('owner_id')) {
            $query->where('owner_type', $request->owner_type)
                ->where('owner_id', $request->owner_id);
        }

        if ($request->has('status')) {
            $query->withStatus($request->status);
        }

        if ($request->boolean('archived', false)) {
            $query->archived();
        } else {
            $query->active();
        }

        $projects = $query->latest()->get();

        $response = response()->json([
            'success' => true,
            'data' => $projects,
        ]);

        return $this->runAfterHooks($request, $response, HookAction::INDEX);
    }

    public function store(Request $request): JsonResponse
    {
        $request = $this->runBeforeHooks($request, HookAction::STORE);

        $validator = Validator::make($request->all(), [
            'owner_type' => 'required|string',
            'owner_id' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status_id' => 'nullable|exists:project_statuses,id',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $project = $this->projectModel()::create([
            'owner_type' => $request->owner_type,
            'owner_id' => $request->owner_id,
            'creator_type' => $request->creator_type,
            'creator_id' => $request->creator_id,
            'status_id' => $request->status_id,
            'title' => $request->title,
            'description' => $request->description,
            'metadata' => $request->metadata,
        ]);

        $response = response()->json([
            'success' => true,
            'data' => $project,
        ], 201);

        return $this->runAfterHooks($request, $response, HookAction::STORE);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $request = $this->runBeforeHooks($request, HookAction::SHOW);

        $project = $this->projectModel()::findOrFail($id);

        $response = response()->json([
            'success' => true,
            'data' => $project,
        ]);

        return $this->runAfterHooks($request, $response, HookAction::SHOW);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $request = $this->runBeforeHooks($request, HookAction::UPDATE);

        $project = $this->projectModel()::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'owner_type' => 'sometimes|string',
            'owner_id' => 'sometimes',
            'status_id' => 'nullable|exists:project_statuses,id',
            'thumbnail_url' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $project->update($request->only([
            'title',
            'description',
            'owner_type',
            'owner_id',
            'status_id',
            'thumbnail_url',
            'metadata',
        ]));

        $response = response()->json([
            'success' => true,
            'data' => $project,
        ]);

        return $this->runAfterHooks($request, $response, HookAction::UPDATE);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $request = $this->runBeforeHooks($request, HookAction::DESTROY);

        $project = $this->projectModel()::findOrFail($id);
        $project->delete();

        $response = response()->json([
            'success' => true,
        ]);

        return $this->runAfterHooks($request, $response, HookAction::DESTROY);
    }

    public function archive(Request $request, string $id): JsonResponse
    {
        $request = $this->runBeforeHooks($request, HookAction::ARCHIVE);

        $project = $this->projectModel()::findOrFail($id);
        $project->archive();

        $response = response()->json([
            'success' => true,
            'data' => $project->fresh(),
        ]);

        return $this->runAfterHooks($request, $response, HookAction::ARCHIVE);
    }

    public function unarchive(Request $request, string $id): JsonResponse
    {
        $request = $this->runBeforeHooks($request, HookAction::UNARCHIVE);

        $project = $this->projectModel()::findOrFail($id);
        $project->unarchive();

        $response = response()->json([
            'success' => true,
            'data' => $project->fresh(),
        ]);

        return $this->runAfterHooks($request, $response, HookAction::UNARCHIVE);
    }
}
