<?php

/**
 * @author DoanLN
 * cho phép tạo ra các đối tượng từ màng, 
 * truy cập cào các phần tử của mảng thông qua key bằng tên thuộc tính của đối tượng
 * 
 */

namespace System\Helpers;
use Countable;
Class Arr implements Countable{
    /**
     * @var array $data
     */
    protected $data = [];
    /**
     * @var $original dử liêu gốc
     */
    protected $original = null;
    /**
     * khoi tao doi tuong
     * @param array|object $data
     */
    function __construct($data = [])
    {
        if(is_array($data) || is_object($data)){
            // gán giá trị ban au629 cho thuộc tính original
            $this->original = $data;

            foreach ($data as $key => $value) {
                // duyệt qua mảng hoặc object để gán key, value ở level 0 cho biến data
                $this->data[$key] = $value;
            }
        }
    }

    /**
     * lấy giá trị phần tử
     * @param string|int $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key,$default = null)
    {
        if(is_null($key)) return $this->data;
        // nếu tồn tải key name trong mang data
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key]; 
        }
        if(count($name_arr = explode('.', $key)) > 1){
            $data = $this->data;
            foreach ($name_arr as $n) {
                if(!is_array($data) || !array_key_exists($n, $data)){
                    return $default;
                }
                $data = $data[$n];
            }
            return $data;
        }
        return $default;

    }

    /**
     * gán giá trị
     * @param string|int $key
     * @param mixed
     * @return object instance
     */
    public function set($key, $value)
    {
        if(is_array($key)){
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        }
        elseif(count($keys = explode('.', $key)) > 1){
            $this->data = $this->fillValue($keys, $value,$this->data);
        }
        else{
            $this->data[$key] = $value;
        }
        
        return $this;
    }

    
    /**
     * push them giá trị vào sau cung của mảng
     * @param mixed $value
     * @param string $key
     * @return Arr instance
     */
    public function push($value, $key = null)
    {
        if(!is_null($key)){
            $arr = $this->get($key);
            if(!is_array($arr)){
                if($arr) $arr = [$arr];
                else{
                    $arr = [];
                }
            }
            $arr[] = $value;
            $this->set($key, $arr);
        }else{
            $this->data[] = $value;
        }
        return $this;
    }

    /**
     * dien tham so vao mang
     * @param string $key
     * @param mixed $value
     * 
     * @param array $array
     * @return array
     */
    protected function fillValue($keys, $value = null, $array = null)
    {
        if($keys){
            $k = array_shift($keys);
            if(!is_array($array)) $array = [];
            if(!count($keys)) {
                $array[$k] = $value;
                
            }else{
                $array[$k] = $this->fillValue($keys, $value, $array[$k]??[]);
            }
        }
        return $array;
        
    }

    
    /**
     * xoa theo mang key
     * @param string $key
     * @param mixed $value
     * 
     * @param array $array
     * @return array
     */
    protected function removeTree($keys, $array = null)
    {
        if($keys){
            $k = array_shift($keys);
            if(!is_array($array)) return $array;
            if(!count($keys)) {
                unset($array[$k]);
                
            }elseif(isset($array[$k]) && is_array($array[$k])){
                $array[$k] = $this->removeTree($keys, $array[$k]);
            }
        }
        return $array;
        
    }    
    
    /**
     * xóa phần tử
     * @param string $keys
     * @return object instance
     */
    public function remove(...$keys)
    {
        if(count($keys)){
            foreach ($keys as $key) {
                if(count($array_key = \explode('.', $key)) > 1){
                    $this->data = $this->removeTree($array_key, $this->data);
                }
                else{
                    unset($this->data[$key]);
                }
            }
        }
        else{
            $this->data = [];
        }
        return $this;
    }

    /**
     * copy các key value và trả về một mảng
     * @param array $keys
     * @return array
     */
    public function copy(array $keys = [])
    {
        if(count($keys)){
            $data = [];
            foreach ($keys as $key) {
                $data[$key] = $this->get($key);
            }
            return $data;
        }
        return $this->data;
    }



    /**
     * tra về mag data
     * @return array
     */
    public function all()
    {
        return $this->data;
    }


        /**
     * đếm phần tử
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * trộn mảng
     * @param array $arrays
     * @return instance
     */
    public function merge(...$arrays)
    {
        $this->data = array_merge($this->data, ...$arrays);
        return $this;
    }

    /**
     * kiểm tra xem có phan tử nào giống giá trị đã cho hay ko
     * @param mixed $value giá trị cần kiểm tra
     * @param string $key key truy cap mang con
     * @return boolean
     */
    public function in($value, $key = null)
    {
        if(!is_null($key)){
            if(is_array($parent = $this->get($key))){
                return in_array($value, $parent);
            }
            return false;
        }
        return in_array($value, $this->data);
    }

    /**
     * kiểm tra xem key hoặc index nào đó có dc set hay ko
     * @param string $key
     * @return bool
     */
    public function isset($key) : bool
    {
        // nếu key tồn tại sẽ trả về true
        if(array_key_exists($key, $this->data)) return true;
        // nếu key là chuỗi hoặc số
        elseif (is_string($key)) {
            // nếu key được phân cách bằng dấu chấm
            if(count($keys = explode('.', $key)) > 1){
                $data = $this->data;
                foreach ($keys as $k) {
                    // nếu $data không phải mảng, hoặc key ko tồn tại trong mảng data thì trả về false
                    if(!is_array($data) || !array_key_exists($k, $data)) return false;
                    else $data = $data[$k];
                }
                return true;
            }
        }
        return false;
    }



    public function __toString()
    {
        return '';
    }

    /**
     * lấy giá trị phần tụ theo tên thuộc tính
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * gan gia tri cho phan tu
     * @param string $key
     * @param mixed $value
     * 
     * @return object
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * kiểm tra tồn tại
     * 
     * @return boolean
     */
    public function  __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * xóa phần tử
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }


    /**
     * gọi hàm với tên thuộc tính với tham số là giá trị default
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        $defaultValue = count($arguments) ? $arguments[0] : null;
        return $this->get($name, $defaultValue);
    }
    
    
    /**
     * chuyển toản bộ object thành array
     * @param object $object
     */
    public static function parse($object)
    {
        $d = $object;
        if (is_object($d)) {
            $d = get_object_vars($d);
        }
    
        if (is_array($d)) {
            return array_map(__METHOD__, $d);
        }
        else {
            return $d;
        }
    }

    /**
     * kiểm tra xem mảng đó có phải chứa key ở level 0 toàn là số hay ko?
     * @param array $array
     * @return boolean
     */
    public static function isNumericKeys(array $array = []) : Bool
    {
        if(is_array($array)){
            if(count($array)){
                $prev = -1;
                foreach ($array as $key => $value) {
                    if(!is_numeric($key) || $key - $prev != 1) return false;
                    $prev++;
                }
                return true;
            }
        }
        return false;
    }

}