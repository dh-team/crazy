<?php
use System\Core\Route;

if(!function_exists('route')){
    function route($name, $params = [])
    {
        if($route = Route::getRouteByName($name)){
            return $route->getUrl($params);
        }
        return null;
    }
}