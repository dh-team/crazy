<?php

namespace System\Http;

use System\Helpers\Arr;
use System\Core\Route;

Class Request{
    /**
     * method priority
     *
     * @var array
     */
    protected $methodPriority = ['post', 'get', 'request', 'route'];
    /**
     * data
     *
     * @var array
     */
    protected static $userData = null;

    public function __construct()
    {
        static::check();
    }
    // kiểm tra xem da dc set hay chua
    protected static function check()
    {
        if(!static::$userData){
            if ($_SERVER['REQUEST_METHOD'] == 'PUT')
            {
                parse_str(file_get_contents("php://input"), $_PUT);

                foreach ($_PUT as $key => $value)
                {
                    unset($_PUT[$key]);

                    $_PUT[str_replace('amp;', '', $key)] = $value;
                }

                $_REQUEST = array_merge($_REQUEST, $_PUT);
            }
            $routeData = [];
            if($route = Route::getActiveRoute()){
                $routeData = $route->getParam();
            }
            static::$userData = new Arr([
                'post' => $_POST,
                'get' => $_GET,
                'request' => $_REQUEST,
                'route' => $routeData,
                'session' => $_SESSION,
                'cookie' => $_COOKIE
            ]);
        }
    }

    /**
     * lay ra route hien tai hoac tham so cua route hien tai
     *
     * @param string|null $key
     * @param mixed $default
     * @return Route|mixed
     */
    public function route($key = null, $default = null)
    {
        $returnData = null;
        if($route = Route::getActiveRoute()){
            if(is_null($key)){
                $returnData = $returnData;
            }else{
                $returnData = $route->getParam($key, $default);
            }
        }else{
            $returnData = $default;
        }
        return $returnData;
    }

    public function __get($name)
    {
        $val = null;
        foreach ($this->methodPriority as $method) {
            $v = static::$userData->get($method . '.' .$name);
            if(!is_null($v)){
                $val = $v;
                break;
            }
        }
        return $val;
    }

    public function all()
    {
        return static::$userData->get('request');
    }


    /**
     * lấy thông tin request theo method
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if(in_array($k = strtolower($name), ['get', 'post', 'request', 'session', 'cookie'])){
            $key = $k;
            if(count($arguments)){
                // lấy ra phẩn tử đầu tiên gán vào key
                // ví dụ post.name
                $key .= '.' . array_pop($arguments);
            }
            return static::$userData->get($key, ...$arguments);
        }
        // tạm thời return null
        return null;
    }
}