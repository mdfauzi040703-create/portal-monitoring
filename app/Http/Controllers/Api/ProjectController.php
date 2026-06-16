<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller {
    public function index() {
        return response()->json(Project::withCount('documents')->get());
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);
        $project = Project::create([
            'name'        => $request->name,
            'description' => $request->description,
            'created_by'  => $request->user()->id,
        ]);
        return response()->json($project, 201);
    }

    public function update(Request $request, Project $project) {
        $request->validate(['name' => 'required|string|max:100']);
        $project->update($request->only('name','description'));
        return response()->json($project);
    }

public function destroy(Project $project) {
    $project->documents()->delete();
    $project->delete();
    return response()->json(['message' => 'Project dihapus']);
}
}