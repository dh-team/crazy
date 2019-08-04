<?php

namespace System\Core;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

class Route {
    /**
     * @var array $supportedMethods các method dc ho tro
     */
    public static $supportedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];


    /**
     * @var array $helpers các method dc ho tro
     */
    public static $helpers = ['group', 'namespace', 'prefix', 'middleware'];


    /**
     * controller namespace
     *
     * @var string
     */
    protected $namespace = 'App\Controllers';

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

    protected static $activeRoute = null;
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
        return $this;
    }

    /**
     * thiet lap duong dan
     *
     * @param string $uri
     * @return void
     */
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
        return $this;
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

    /**
     * set callback
     *
     * @param string|function $callback
     * @return void
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * dat ten cho route
     *
     * @param string $name
     * @return void
     */
    public function name($name = null)
    {
        if(is_null($name)) return $this->routeName;
        $this->routeName = $name;
        static::$routes['names'][$name] = $this->index;
        return $this;
    }

    public function setIndex($index = -1)
    {
        $this->index = $index;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function getCallback()
    {
        $callback = $this->callback;
        $callData = null;
        if(is_callable($callback)){
            $callData = $callback;
        }
        elseif (is_string($callback)) {
            $class = null;
            $method = null;
            // trường hợp callback = 'ExampleController->method'
            if(count($arrow = explode('->', $callback)) == 2){
                $class = $arrow[0];
                $method = $arrow[1];
            }
            // trường hợp callback = 'ExampleController@method'
            elseif(count($a = explode('@', $callback)) == 2){
                $class = $a[0];
                $method = $a[1];
            }

            if($class){
                if(class_exists($class)){
                    $ctrl = $class;
                }elseif (class_exists($c = $this->namespace . '\\' . $class)) { // kiểm tra class với namespace
                    $ctrl = $c;
                }else{
                    $ctrl = $this->namespace . '\\' . 'DefaultController';
                }
                
                if (method_exists($ctrl,  '__construct')) 
                { 
                    
                
                // khoi tao object
                //$refMethod = new ReflectionMethod($ctrl,  '__construct'); 
                //$params = $refMethod->getParameters(); 
            
                // $re_args = array(); 
                /*
                foreach($params as $key => $param) 
                { 
                    if ($param->isPassedByReference()) 
                    { 
                        $re_args[$key] = &$args[$key]; 
                    } 
                    else 
                    { 
                        $re_args[$key] = $args[$key]; 
                    } 
                } 
            
                */
                // $refClass = new ReflectionClass($class_name); 
                // $class_instance = $refClass->newInstanceArgs((array) $re_args); 
                }
                //$rc = new ReflectionClass($ctrl);
                // goi ham construct
                //$controller = $rc->newInstanceArgs( [] );
                $callData = [
                    'type' => 'controller',
                    'class' => $ctrl,
                    'method' => $method
                ];
            }else{
                $callData = [
                    'type' => 'function',
                    'function' => $this->namespace . '\\' . 'DefaultController::notDefined'
                ];
            }
        }else{
            $callData = [
                'type' => 'function',
                'function' => $this->namespace . '\\' . 'DefaultController::notDefined'
            ];
        }
        return $callData;
    }
    
    /**
     * thực thi route
     */
    public function run()
    {
        $callable = $this->getCallback();
        $routeParams = $this->values;
        if($callable['type'] == 'controller'){
            if(!method_exists($callable['class'],  $callable['method'])){
                throw new \Exception("Phuong thuc $callable[controller]::$callable[method] khong ton tai");
                die;
            }
            
            $class = new ReflectionClass($callable['class']);
            $method = $class->getMethod($callable['method']);
            $params = $method->getParameters();
            
            $parameterTypes = [];
            
            if(count($params)){
                $refParam = new ReflectionParameter([$callable['class'],  $callable['method']], 0);
                $type = trim($refParam->getType());
                if($type && class_exists($type)){
                    $ref = new ReflectionClass($type);
                    $firstParam = $ref->newInstanceArgs([]);
                    array_unshift($routeParams, $firstParam); 
                }
            }
            $controller = $class->newInstanceArgs([]);
            $call = [$controller, $callable['method']];
        }else{
            $call = $callable['function'];
        }
        

        return call_user_func_array($call, $routeParams);
    }

    /**
     * tạo ra url
     * @param array $params
     * @return string
     */
    public function getUrl($params = [])
    {
        $uri = $this->uri;
        $queryStringParams = []; // ?name=a
        $matchCount = 0;
        if(!$this->hasParam){
            $queryStringParams = $params;
        }elseif(count($params)){
            //print_r($params);
            // /print_r($this->match);
            foreach ($params as $key => $value) {
                if(array_key_exists($key, $this->match)){
                    $matchCount++;
                    $uri = str_replace($this->match[$key], $value, $uri);
                }else{
                    $queryStringParams[$key] = $value;
                }
            }
        }
        
        if(count($this->params) != $matchCount){
            throw new \Exception("Route thiếu tham số");
            return null;
        }
        if(count($queryStringParams)){
            $uri.='?';
            foreach ($queryStringParams as $key => $value) {
                $uri.=$key .'='. urlencode($value).'&';
            }
            $uri = rtrim($uri, '&');
        }
        return App::getUrl($uri);
    }

    /**
     * lay gia tri tham so
     *
     * @param string $key
     * @param mixed $default
     * @return void
     */
    public function getParam(string $key = null, $default = null)
    {
        return is_null($key) ? $this->data : ($this->data[$key] ?? $default);
    }

    /**
     * thêm route
     * @param Route $route
     */
    protected static function addRoute(Route $route)
    {
        // lấy index để truy xuất route theo tên cho nhanh khi cần
        $index = count(static::$routes['list']);
        // thêm route
        $route->setIndex($index);
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

    /**
     * khai bao mot rout chap nhan tat ca cac http method
     *
     * @param mixed ...$params
     * @return void
     */
    public static function any(...$params){
        return static::setRoute('*', ...$params);
    }

    public static function custom(...$params){
        return static::setRoute(...$params);
    }




    /**
     * gọi phương thức static chưa được khai báo
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $name = strtoupper($name);
        if(in_array($name, static::$supportedMethods)){
            return static::setRoute($name, ...$arguments);
        }
    }


    /**
     * lấy ra route trùng khớp với url đầu tiên
     *
     * @param string $pathinfo
     * @return Route|null
     */
    public static function first($pathinfo)
    {
        if(count(static::$routes['list'])){
            foreach (static::$routes['list'] as $route) {
                if($route->compareUri($pathinfo)) {
                    static::$activeRoute = $route;
                    return $route;
                }
            }
        }
        return null;
    }

    /**
     * lấy route theo route name
     *
     * @param string $name
     * @return Route|null
     */
    public static function getRouteByName($name)
    {
        if(array_key_exists($name, static::$routes['names'])){
            $index = static::$routes['names'][$name];
            if(array_key_exists($index, static::$routes['list'])){
                return static::$routes['list'][$index];
            }
        }
        return null;
    }

    public static function getActiveRoute()
    {
        return static::$activeRoute;
    }

    public static function all()
    {
        return static::$routes['list'];
    }
}

