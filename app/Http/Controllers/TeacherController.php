<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $teachers = Teacher::with(['user', 'class'])
            ->when($search, function ($query, $search) {
                $query->where('nip', 'like', "%$search%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                      });
            })
            ->get();

        return response()->json($teachers);
    }

    public function show(Request $request, $id)
    {
        $search = $request->query('search');

        $teacher = Teacher::with(['user', 'class'])
            ->when($search, function ($query, $search) {
                $query->where('nip', 'like', "%$search%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                      });
            })
            ->findOrFail($id);

        return response()->json($teacher);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'nip' => 'required|unique:teachers,nip',
        ]);

        $validated['id'] = Str::uuid();

        $teacher = Teacher::create($validated);
        return response()->json($teacher, 201);
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'class_id' => 'sometimes|exists:classes,id',
            'nip' => 'sometimes|unique:teachers,nip,' . $id,
        ]);

        $teacher->update($validated);
        return response()->json($teacher);
    }

    public function destroy($id)
    {
        Teacher::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}