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
    public function run($dir)
    {
        $request_uri = explode('?', $_SERVER['REQUEST_URI']);

        $uri = $request_uri[0];
        $root = $_SERVER['DOCUMENT_ROOT'];
    
        $path = trim(str_replace($root, '', $dir), '/');
    
        $pathinfo = trim(preg_replace('#^'.trim($path, '/').'#i', '', trim($uri, '/')), '/');

        static::$data['request'] =compact('uri', 'pathinfo');
        static::$data['paths'] = [
            'system_path' => $path,
            'public_path' => $dir,
        ];
        
    }
    
}