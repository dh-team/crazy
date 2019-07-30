<?php

namespace System\Http;

use System\Helpers\Arr;

Class Request{
    /**
     * method priority
     *
     * @var array
     */
    protected $methodPriority = ['post', 'get', 'request'];
    /**
     * data
     *
     * @var array
     */
    protected static $userData = null;

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
            static::$userData = new Arr([
                'post' => $_POST,
                'get' => $_GET,
                'request' => $_REQUEST,
                'session' => $_SESSION,
                'cookie' => $_COOKIE
            ]);
        }
    }

    public function __get($name)
    {
        static::check();
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
        static::check();
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
        static::check();
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