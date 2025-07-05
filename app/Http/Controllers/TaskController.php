<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $tasks = Task::with('class')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
            })
            ->get();

        return response()->json($tasks);
    }

    public function show(Request $request, $id)
    {
        $search = $request->query('search');

        $task = Task::with('class')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
            })
            ->findOrFail($id);

        return response()->json($task);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'due_date' => 'nullable|date',
            'media' => 'nullable|file|mimes:pdf,doc,docx,zip,jpg,jpeg,png', // tambahkan validasi file
        ]);

        $validated['id'] = Str::uuid();

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $path = $file->store('task-media', 'public'); 
            $validated['media_path'] = $path;
        }

        $task = Task::create($validated);

        return response()->json($task, 201);
    }


    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'sometimes|string',
            'class_id' => 'sometimes|exists:classes,id',
            'due_date' => 'sometimes|date',
            'media' => 'nullable|file|mimes:pdf,doc,docx,zip,jpg,jpeg,png', // tambahkan validasi file
        ]);

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $path = $file->store('task-media', 'public');
            $validated['media_path'] = $path;
        }

        $task->update($validated);

        return response()->json($task);
    }


    public function destroy($id)
    {
        Task::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
