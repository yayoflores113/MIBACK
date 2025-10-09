<?php

//use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Route;

//$role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
//$role = Role::firstOrCreate(['name' => 'user',  'guard_name' => 'web']);

Route::get('/', function () {
    return view('welcome');
});
