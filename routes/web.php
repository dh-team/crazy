<?php
use System\Core\Route;

Route::get('test', function(){
    echo 'test';
});
Route::get('demo', function(){
    echo 'demo';
});
Route::get('user/{name}/{age}/{gender}.html', 'DefaultController@info');

Route::delete('delete', function(){
    echo 'demo';
});
Route::put('put', function(){
    echo 'put';
});
Route::custom(['get', 'post'], 'custom', function(){
    
});

Route::get('login.html', 'DemoFormController@getForm');
Route::post('login', 'DemoFormController@login');
Route::put('login', 'DemoFormController@login');
