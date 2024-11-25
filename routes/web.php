<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/recipes/images/{filename}', function ($filename) {
    $path = storage_path("app/public/recipes/images/{$filename}");
    if (!file_exists($path)) {
        abort(404);
    }
    return Response::file($path);
});

