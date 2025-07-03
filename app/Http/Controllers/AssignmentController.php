<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $assignments = Assignment::with(['user', 'task'])
            ->when($search, function ($query, $search) {
                $query->where('score', 'like', "%$search%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                      });
            })
            ->get();

        return response()->json($assignments);
    }

    public function show(Request $request, $id)
    {
        $search = $request->query('search');

        $assignment = Assignment::with(['user', 'task'])
            ->when($search, function ($query, $search) {
                $query->where('score', 'like', "%$search%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                      });
            })
            ->findOrFail($id);

        return response()->json($assignment);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'task_id' => 'required|exists:tasks,id',
            'score' => 'nullable|integer',
            'submitted_at' => 'nullable|date',
        ]);

        $validated['id'] = Str::uuid();

        $assignment = Assignment::create($validated);
        return response()->json($assignment, 201);
    }

    public function update(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'task_id' => 'sometimes|exists:tasks,id',
            'score' => 'sometimes|integer',
            'submitted_at' => 'sometimes|date',
        ]);

        $assignment->update($validated);
        return response()->json($assignment);
    }

    public function destroy($id)
    {
        Assignment::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
