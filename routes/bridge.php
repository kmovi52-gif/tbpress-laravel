<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// public pages and signed server calls: deliberately outside the "web" group (no session / CSRF / cookies)
Route::match(['get', 'post'], '/news-bridge', fn (Request $r) => \NewsBridge::laravel($r));
Route::get('/epaper', fn (Request $r) => \NewsBridge::laravel($r, 'epaper'));
Route::get('/verify', fn (Request $r) => \NewsBridge::laravel($r, 'verify'));
Route::get('/apply/{form}', function (Request $r, $form) {
    $r->query->set('apply', (string) (int) $form);

    return \NewsBridge::laravel($r, 'apply');
})->whereNumber('form');
