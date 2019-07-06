<?php

namespace System\Core;

class App {
    protected static $data = [
        'paths' => [],
        'request' => []
    ];


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
    
        $pathinfo = trim(preg_replace('#^'.trim($path, '/').'#i', '', trim($uri, '/')), '/');

        static::$data['request'] =compact('uri', 'pathinfo');
        static::$data['paths'] = [
            'system' => dirname($dir),
            'public' => $dir,
        ];

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
            if(is_string($response) || is_numeric($response)) echo $response;
            else echo json_decode($response);
        }
        else echo '<h3>404 - not found</h3>';
    }

    public static function setWebRoute()
    {
        require static::path('system') .'/routes/web.php';
    }

    public static function path($key)
    {
        return array_key_exists($key, static::$data['paths']) ? static::$data['paths'][$key]:null;
    }
}