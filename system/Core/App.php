<?php

namespace System\Core;

use Jenssegers\Blade\Blade;

class App {
    protected static $data = [
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

        static::$data['request'] = compact('uri', 'pathinfo');
        $base = dirname($dir);
        $paths = [
            'base' => $base,
            'system' => $base.'/system',
            'public' => $dir,
            'views' => $base. '/views',
            'view_cache' => $base. '/storage/views',
            
        ];
        static::$data['paths'] = $paths;
        
        View::start($paths['views'], $paths['view_cache']);


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
        return array_key_exists($key, static::$data['paths']) ? static::$data['paths'][$key]:null;
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