namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $query = Classroom::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%");
        }

        return response()->json($query->get());
    }

    public function show($id, Request $request)
    {
        $class = Classroom::where('id', $id)
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })->firstOrFail();

        return response()->json($class);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

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
        $class = Classroom::findOrFail($id);
        $class->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
