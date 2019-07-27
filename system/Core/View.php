<?php 
namespace System\Core;

use Jenssegers\Blade\Blade;

Class View{
    protected static $data = [];
    protected static $engine = null;
    
    /**
     * thiết lập view Engine
     *
     * @param string $blade_path
     * @param string $cache_path
     * @return void
     */
    public static function start(string $blade_path, string $cache_path)
    {
        static::$engine = new Blade($blade_path, $cache_path);
    }

    /**
     * chia sẻ data để dùng cho view
     *
     * @param string|array $name
     * @param mixed $value
     * @return void
     */
    public static function share($name, $value = null)
    {
        static::$data = array_merge(static::$data, static::parseData($name, $value));
    }

    /**
     * chuẩn hóa data
     *
     * @param string|array $name
     * @param mixed $value
     * @return array
     */
    public static function parseData($name, $value = null)
    {
        $data = [];
        // nếu là mảng
        if(is_array($name)){
            foreach ($name as $key => $val) {
                if(preg_match('/^[A-z_]+[A-z0-8_]*/i', $key)){
                    $data[$key] = $val;
                }
            }
        }
        // nếu neme là chuỗi
        elseif(preg_match('/^[A-z_]+[A-z0-8_]*/i', $name)){
            $data[$name] = $val;
        }
        return $data;
    }

    /**
     * tạo một view
     *
     * @param string $blade_filename
     * @param array $data
     * @return void
     */
    public static function make($blade_filename, array $data = [])
    {

        return static::$engine->make(
            $blade_filename, 
            array_merge(static::$data, static::parseData($data))
        );
    }
}
