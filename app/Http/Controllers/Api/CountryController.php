<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        // Devolvemos lo necesario para el dropdown
        $rows = DB::table('countries')
            ->orderBy('name')
            ->get(['id','name','code']);

        return response()->json($rows, 200);
    }
}
