<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTestRequest;
use App\Http\Requests\UpdateTestRequest;
use App\Models\Test;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /** Trae el test activo más reciente (por versión) */
    public function active()
    {
        $test = Test::where('is_active', true)->orderByDesc('version')->first();
        return response()->json($test);
    }

    public function index(Request $request)
    {
        $q = Test::query();
        if (($act = $request->boolean('is_active', null)) !== null) {
            $q->where('is_active', $act);
        }
        return response()->json($q->orderByDesc('id')->paginate(20));
    }

    public function store(StoreTestRequest $request)
    {
        $t = Test::create($request->validated());
        return response()->json($t, 201);
    }

    public function show(Test $test)
    {
        return response()->json($test);
    }

    public function update(UpdateTestRequest $request, Test $test)
    {
        $test->update($request->validated());
        return response()->json($test);
    }

    public function destroy(Test $test)
    {
        $test->delete();
        return response()->json(['deleted' => true]);
    }
}
