<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10); 
        $classes = Classroom::when($search, function ($query, $search) {
            $query->where('name', 'like', "%$search%");
        })->paginate($perPage);

        return response()->json($classes);
    }

    public function show(Request $request, $id)
    {
        $search = $request->query('search');

        $class = Classroom::when($search, function ($query, $search) {
            $query->where('name', 'like', "%$search%");
        })->findOrFail($id);

        return response()->json($class);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $validated['id'] = Str::uuid();

        $class = Classroom::create($validated);
        return response()->json($class, 201);
    }

    public function update(Request $request, $id)
    {
        $class = Classroom::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
        ]);

        $class->update($validated);
        return response()->json($class);
    }

    public function destroy($id)
    {
        Classroom::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}