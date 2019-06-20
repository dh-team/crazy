<?php

namespace System\Core;
use ReflectionClass;

class Route {
    /**
     * @var array $supportedMethods các method dc ho tro
     */
    public static $supportedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
    /**
     * @var string $uri
     */
    protected $uri = null;

    /**
     * @var array $match mảng trùng khớp tham số 'name' => '{name}'
     */
    protected $match = [];

    /**
     * @var array $params danh sach ten tham so
     */
    protected $params = [];

    /**
     * @var bool $hasParam route co chua tham so hay khong
     */
    protected $hasParam = false;

    /**
     * @var string $paramUri
     */
    protected $paramUri = null;

    /**
     * @var array $values danh sach gia tri
     */
    protected $values = [];
    
    /**
     * @var array $data mang tham so va gia tri [paramName => value]
     */
    protected $data = [];

    /**
     * @var array $methods [get, post, put, patch, delete, options]
     */
    protected $methods = [];

    /**
     * @var callable $callback
     */
    protected $callback = null;

    /**
     * @var string $name Dinh danh route de goi khi can
     */
    protected $routeName = null;

    /**
     * @var int $index
     */
    protected $index = -1;

     /**
     * @var array $routes
     */
    public static $routes = [
        // index => Route
        'list' => [],
        // name => index
        'names' => []
    ];

    /**
     * ham khời tạo
     * @param string|array $method
     * @param string $uri 
     * @param callable|string $callback
     * @param string $name 
     * 
     */
    function __construct($method, $uri, $callback, $name = null)
    {
        $this->setMethod($method);
        $this->setUri($uri);
        $this->callback = $callback;
        $this->name($name);
    }

    /**
     * set method
     * @param array|string $method
     */
    public function setMethod($method)
    {
        $methods = [];
        if(is_array($method)){
            $methods = array_filter(array_map('strtoupper', $method), function($method){
                return in_array($method, Route::$supportedMethods);
            });
        }elseif (is_string($method)) {
            $method = strtoupper($method);
            if(in_array($method, static::$supportedMethods)){
                $methods[] = $method;
            }elseif(in_array($method, ['ALL', '*'])){
                $methods = static::$supportedMethods;
            }
        }
        if($methods){
            $this->methods = $methods;
        }else{
            $this->methods = ['GET'];
        }
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
        // lấy ra tên biến nếu có
        if(preg_match_all('#\{([A-z0-9_]*)\}#i', $uri, $varMatch)){
            $this->hasParam = true;
            // danh sach tên biến
            $this->params = $varMatch[1];

            // mảng tên biến trỏ đến chuỗi khớp uri
            $this->match = array_combine($varMatch[1], $varMatch[0]);
            
            // đưa route uri về biểu thức regex
            $this->paramUri = preg_replace('#\{([A-z0-9_]*)\}#i', '([^/]*)', $uri);
        }
    }
    /**
     * so sanh pathinfo va uri da khai bao
     * @param $pathinfo
     */
    public function compareUri($pathinfo)
    {
        if(!$this->compareMethod()) return null;
        if(!$this->hasParam){
            // so sánh trùng khớp để lấy data
            if(preg_match_all('#'.$this->uri.'#i', $pathinfo)){
                return true;
            }
            return false;
        }
        // so sánh trùng khớp để lấy data
        if(preg_match_all('#'.$this->paramUri.'#i', $pathinfo, $dataMatch)){
            $valueList = [];
            // nếu khớp sẽ lấy data từ uri
            for($i = 1; $i < count($dataMatch); $i++){
                $valueList[$i-1] = $dataMatch[$i][0];
            }
            $this->values = $valueList;
            // đổ vào mảng uri data
            $this->data = array_combine($this->params, $valueList);
            return true;
        }
        return false;
    }

    /**
     * so sanh method
     */
    public function compareMethod($method = null)
    {
        if(!$method) $method = $_SERVER['REQUEST_METHOD'];
        return in_array(strtoupper($method), $this->methods);
    }

    public function setCallback($callback)
    {
        if(is_callable($callback)) $this->callback = $callback;
        elseif (is_string($callback)) {
            $class = null;
            $method = null;
            // trường hợp callback = 'ExampleController->method'
            if(count($arrow = explode('->', $callback)) == 2){
                $class = $arrow[0];
                $method = $arrow[1];
            }
            // trường hợp callback = 'ExampleController@method'
            elseif(count($a = explode('->', $callback)) == 2){
                $class = $a[0];
                $method = $a[1];
            }
            if($class && class_exists($class)){
                // khoi tao object
                $rc = new ReflectionClass($class);
                // goi ham construct
                $controller = $rc->newInstanceArgs( [] );
                $this->callback = [$controller, $method];
            }else{
                $this->callback = ['DefaultController', 'index'];
            }
        }else{
            $this->callback = ['DefaultController', 'index'];
        }
    }

    public function name($name)
    {
        $this->name = $name;
        static::$routes['names'][$this->index] = $name;
    }

    public function setIndex($index = -1)
    {
        $this->index = $index;
    }

    public function getRouteName()
    {
        return $this->name;
    }

    /**
     * thực thi route
     */
    public function run()
    {
        return call_user_func_array($this->callback, $this->values);
    }


    protected static function addRoute(Route $route)
    {
        // lấy index để truy xuất route theo tên cho nhanh khi cần
        $index = count(static::$routes['list']);
        // thêm route
        static::$routes['list'][$index] = $route;
        // kiểm tra tên
        if($name = $route->getRouteName()){
            static::$routes['names'][$name] = $index;
        }
    }
    /**
     * set route
     * @param string|array $method
     * @param string $uri 
     * @param callable|string $callback
     * @param string $name 
     * 
     */

    public static function setRoute($method, $uri, $callback, $name = null){
        $route = new static($method, $uri, $callback, $name);
        static::addRoute($route);
        return $route;
    }

    public static function any(...$params){
        return static::setRoute('*', ...$params);
    }

    public static function custom(...$params){
        return static::setRoute(...$params);
    }

    

    public static function __callStatic($name, $arguments)
    {
        return static::setRoute($name, ...$arguments);
    }

    public static function first($pathinfo)
    {
        if(count(static::$routes['list'])){
            foreach (static::$routes['list'] as $route) {
                if($route->compareUri($pathinfo)) return $route;
            }
        }
        return null;
    }
}

