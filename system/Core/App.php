<?php

namespace System\Core;

use System\Database\DB;

class App {
    protected static $config = [
        'paths' => [],
        'request' => []
    ];

    protected static $url = '/';

    /**
     * chạy ứng dụng
     * @param string $dir
     */
    public static function run($dir)
    {
        
        $request_uri = explode('?', $_SERVER['REQUEST_URI']);
        $uri = $request_uri[0];
        $root = $_SERVER['DOCUMENT_ROOT'];
        $path = trim(str_replace($root, '', $dir), '/');
        static::$url = 'http://'.$_SERVER['HTTP_HOST'] .'/'.ltrim($path, '/');
        $pathinfo = trim(preg_replace('#^'.trim($path, '/').'#i', '', trim($uri, '/')), '/');
        static::$config['request'] = compact('uri', 'pathinfo');

        // config
        $base = dirname($dir);
        $paths = [
            'base' => $base
        ];
        $configDir = $base.'/config/';
        $configPaths = require ($configDir.'path.php');
        foreach ($configPaths as $key => $value) {
            $paths[$key] = $base .'/' .ltrim($value);
        }

        static::$config['paths'] = $paths;

        static::$config['database'] = require $configDir . 'database.php';
        
        View::start($paths['views'], $paths['view_cache']);

        DB::config(static::$config['database']);

        static::setWebRoute();

        static::fetchRoute($pathinfo);
    }

    /**
     * bắt route
     * @param string $pathinfo
     * @return mixed
     */
    public static function fetchRoute($pathinfo)
    {
        if($router = Route::first($pathinfo)){
            $response = $router->run();
            if(is_string($response) || is_numeric($response) || is_a($response, 'Blade')) echo $response;
            else echo json_decode($response);
        }
        else echo '<h3>404 - not found</h3>';
    }

    public static function setWebRoute()
    {
        require static::path('base') .'/routes/web.php';
    }

    public static function path($key)
    {
        return array_key_exists($key, static::$config['paths']) ? static::$config['paths'][$key]:null;
    }

    public static function getUrl($path = null)
    {
        $url = rtrim(static::$url, '/');
        if($path){
            $url .= '/'. ltrim($path);
        }
        return $url;
    }
}