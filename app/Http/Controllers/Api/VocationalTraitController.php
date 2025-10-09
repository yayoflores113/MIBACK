<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVocationalTraitRequest;
use App\Http\Requests\UpdateVocationalTraitRequest;
use App\Models\VocationalTrait;
use Illuminate\Http\Request;

class VocationalTraitController extends Controller
{
    public function index(Request $request)
    {
        $q = VocationalTrait::query();
        if ($s = $request->string('q')->toString()) {
            $q->where('name', 'like', "%{$s}%")
                ->orWhere('code', 'like', "%{$s}%");
        }
        return response()->json($q->orderBy('name')->get());
    }

    public function store(StoreVocationalTraitRequest $request)
    {
        $t = VocationalTrait::create($request->validated());
        return response()->json($t, 201);
    }

    public function show(VocationalTrait $vocationalTrait)
    {
        return response()->json($vocationalTrait);
    }

    public function update(UpdateVocationalTraitRequest $request, VocationalTrait $vocationalTrait)
    {
        $vocationalTrait->update($request->validated());
        return response()->json($vocationalTrait);
    }

    public function destroy(VocationalTrait $vocationalTrait)
    {
        $vocationalTrait->delete();
        return response()->json(['deleted' => true]);
    }
}
