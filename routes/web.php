<?php
use System\Core\Route;

Route::get('test', function(){
    echo 'test';
});
Route::get('demo', function(){
    echo 'demo';
});
Route::get('user/{name}/{age}/{gender}', function($nane, $age, $gender){
    return 'Xin chào! Tôi tên là '.$nane .', ' .$age .' tuổi, giới tính: '.$gender;
});

Route::delete('delete', function(){
    echo 'demo';
});
Route::put('put', function(){
    echo 'put';
});
Route::custom(['get', 'post'], 'custom', function(){
    
});

echo '<pre>';
if($route = Route::first($pathinfo)){
    $response = $route->run();
    if(is_string($response)){
        echo $response;
    }else{
        print_r($response);
    }
}