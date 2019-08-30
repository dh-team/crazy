<?php

namespace System\Database;

use Crazy\Html\ColumnItem;

class DB{
    /**
     * connevt
     *
     * @var Connwction
     */
    protected static $connection = null;
    /**
     * cau hinh db
     *
     * @var array
     */
    protected static $config = [];
    /**
     * cau hinh
     *
     * @param array $params
     * @return void
     */
    public static function config($params = []){
        static::$config = $params;
    }

    /**
     * kiem tra ket noi
     * @return Connection
     */
    public static function getConnection()
    {
        if(!static::$connection){
            static::$connection = new Connection(static::$config);
        }
        return (static::$connection->isConnect) ? static::$connection : null;
    }

}