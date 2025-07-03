<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $students = Student::with(['user', 'class'])
            ->when($search, function ($query, $search) {
                $query->where('nim', 'like', "%$search%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                      });
            })
            ->get();

        return response()->json($students);
    }

    public function show(Request $request, $id)
    {
        $search = $request->query('search');

        $student = Student::with(['user', 'class'])
            ->when($search, function ($query, $search) {
                $query->where('nim', 'like', "%$search%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                      });
            })
            ->findOrFail($id);

        return response()->json($student);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'nim' => 'required|unique:students,nim',
        ]);

        $validated['id'] = Str::uuid();

        $student = Student::create($validated);
        return response()->json($student, 201);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'class_id' => 'sometimes|exists:classes,id',
            'nim' => 'sometimes|unique:students,nim,' . $id,
        ]);

        $student->update($validated);
        return response()->json($student);
    }

    public function destroy($id)
    {
        Student::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
