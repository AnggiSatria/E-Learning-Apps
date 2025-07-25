<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ClassMember;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page'); 
        $page = $request->query('page');

        $query = Task::with('class')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
            });
        
            if ($perPage && $page) {
                $tasks = $query->paginate($perPage, ['*'], 'page', $page);
            } else {
                $tasks = $query->get();
            }
        

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

    public function getByClassId(Request $request, $classId)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page'); 
        $page = $request->query('page');

        $query = Task::with('class')
            ->where('class_id', $classId)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
                });
            });

            if ($perPage && $page) {
                $tasks = $query->paginate($perPage, ['*'], 'page', $page);
            } else {
                $tasks = $query->get();
            }

        return response()->json($tasks);
    }

    public function getByClassAndUser(Request $request, $classId, $userId)
    {
        $search = $request->query('search');

        // Cek apakah user adalah member dari class tersebut
        $isMember = ClassMember::where('class_id', $classId)
            ->where('user_id', $userId)
            ->exists();

        if (!$isMember) {
            return response()->json(['message' => 'User is not a member of this class.'], 403);
        }

        $tasks = Task::with('class')
            ->where('class_id', $classId)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
                });
            })
            ->get();

        return response()->json($tasks);
    }

    public function getTasksByUserId(Request $request, $userId)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page'); 
        $page = $request->query('page');

        // Ambil semua class_id yang diikuti oleh user
        $classIds = ClassMember::where('user_id', $userId)->pluck('class_id');

        // Ambil semua task dari class yang diikuti user
        $query = Task::with('class')
            ->whereIn('class_id', $classIds)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
                });
            });

            if ($perPage && $page) {
                $tasks = $query->paginate($perPage, ['*'], 'page', $page);
            } else {
                $tasks = $query->get();
            }

        return response()->json($tasks);
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

        $task->load('class');

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

        $task->load('class');

        return response()->json($task);
    }


    public function destroy($id)
    {
        Task::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
