<?php

namespace App\Http\Controllers;

use App\Models\ClassMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClassMemberController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $members = ClassMember::with(['user', 'class'])
            ->when($search, function ($query, $search) {
                $query->where('role', 'like', "%$search%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                      });
            })
            ->get();

        return response()->json($members);
    }

    public function show(Request $request, $id)
    {
        $search = $request->query('search');

        $member = ClassMember::with(['user', 'class'])
            ->when($search, function ($query, $search) {
                $query->where('role', 'like', "%$search%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                      });
            })
            ->findOrFail($id);

        return response()->json($member);
    }

    public function getByUserId(Request $request, $userId)
    {
        $search = $request->query('search');
        $page = $request->query('page');
        $perPage = $request->query('per_page');

        $query = ClassMember::with('class')
            ->where('user_id', $userId)
            ->when($search, function ($query, $search) {
                $query->whereHas('class', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                });
            });

        // Jika ada pagination
        if ($page || $perPage) {
            $perPage = $perPage ?? 10;
            $classMembers = $query->paginate($perPage);

            $transformed = $classMembers->getCollection()->map(function ($item) {
                return $item->class;
            });

            $classMembers->setCollection($transformed);

            return response()->json($classMembers);
        }

        // Tanpa pagination
        $classMembers = $query->get()->map(function ($item) {
            return $item->class;
        });

        return response()->json($classMembers);
    }

    public function getByClassId(Request $request, $classId)
    {
        $search = $request->query('search');

        $members = ClassMember::with(['user', 'class']) // relasi user & class
            ->where('class_id', $classId)
            ->when($search, function ($query, $search) {
                $query->where('role', 'like', "%$search%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%");
                    });
            })
            ->get();

        return response()->json($members);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'role' => 'required|in:student,teacher',
        ]);

        $validated['id'] = Str::uuid();

        $member = ClassMember::create($validated);
        return response()->json($member, 201);
    }

    public function update(Request $request, $id)
    {
        $member = ClassMember::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'class_id' => 'sometimes|exists:classes,id',
            'role' => 'sometimes|in:student,teacher',
        ]);

        $member->update($validated);
        return response()->json($member);
    }

    public function destroy($id)
    {
        ClassMember::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
