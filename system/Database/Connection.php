<?php

/**
 * @author Doanln
 * @copyright 2018
 */

namespace System\Database;

use PDO;
use Doctrine\DBAL\Driver\PDOException;
use PDOStatement;

/**
 * pdo null
 */ 


define('PDODBNULL','<!--?% Doan Dep Trai %?--!>');

class Connection{

    /**
     * doi tuong PDO
     *
     * @var PDO
     */
    protected $pdo;
    /**
     * Host
     *
     * @var string
     */
    protected $host;
    /**
     * database name
     *
     * @var string
     */
    protected $name;
    /**
     * user
     *
     * @var string
     */
    protected $user;
    /**
     * password
     *
     * @var string
     */
    protected $pass;
    /**
     * hệ quản trị CSDL
     *
     * @var string
     */
    protected $DBMS = 'MYSQL';
    /**
     * Danh sach ho tro
     *
     * @var array
     */
    protected $dbmsList = ['MYSQL', 'MSSQL','OROCLE', 'SQLITE'];
    /**
     * Query
     *
     * @var string
     */
    protected $query = '';
    /**
     * Tham so truy van
     *
     * @var array
     */
    protected $params = [];
    
    /**
     * mang key cua cac tham so
     *
     * @var array
     */
    protected $paramKeys = [];
    
    /**
     * pdo statement
     *
     * @var PDOStatement
     */
    protected $stmt;
    /**
     * Trang thai ket noi
     *
     * @var boolean
     */
    public $isConnect = false;
    
    /**
     * Thong bao loi
     *
     * @var string
     */
    protected $errorMessage = null;
    /**
     * Cap do thong bao
     *
     * @var int
     */
    protected $reportLevel = 0;
    
    
    /**
     * ham khoi tao doi tuong pdodb
     * @param string $host
     * @param string $dbname ten csdl
     * @param string $user ten nguoi dung
     * @param string $pass mat khau de truy cap csdl
     * 
     * @date 2018-12-15
     * 
     * @author Doanln
     */ 
    
    public function __construct($host = 'localhost', $name = 'test', $user = 'root', $pass = '', $dbms='MYSQL') {
        if(is_array($host)){
            $h = isset($host['host'])?$host['host']:'localhost';
            foreach($host as $key => $value){
                $$key = $value;
            }
            $host = $h;
        }
        $this->host = $host;
        $this->name = $name;
        $this->user = $user;
        $this->pass = $pass;
        $this->DBMS = $dbms;
        $this->connect();
    }
    
    /**
     * ham khoi tao doi tuong pdodb
     * @param string $host
     * @param string $dbname ten csdl
     * @param string $user ten nguoi dung
     * @param string $pass mat khau de truy cap csdl
     * 
     * @date 2017-07-15
     * 
     * @author Doanln
     */ 
    
    public function connect($host = null, $name = null, $user = null, $pass = null, $dbms=null) {
        
        $h = is_null($host)    ? $this->host : $host;
        $d = is_null($name)    ? $this->name : $name;
        $u = is_null($user)    ? $this->user : $user;
        $p = is_null($pass)    ? $this->pass : $pass;
        $m = is_null($dbms)    ? $this->DBMS : $dbms;
        
        
        $this->isConnect = false;
        try{
            $this->pdo = new PDO('mysql:host='.$h.';dbname='.$d.";charset=utf8",$u,$p);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->isConnect = true;
            
        }
        catch(Exception $e){
            $this->errorMessage = "\nCannot connect to Database \n <!-- ".$e->getMessage().' -->';
            $this->reportError();
        }
        return $this->isConnect;
    }
    
    /**
     * dong ket noi
     */ 
    
    public function disconnect(){
        $this->pdo = null;
    }
    
    public function __destruct(){
        $this->pdo = null;
    }
    
    /**
     * t?o b?n sao pdo
     */ 
    
    public function copy(){
        return clone $this;
    }
    
    
    /**
     * ham hien thi thong bao loi. co the tuy chinh dung code, bo qua, hay van hien thong bao va tiep tuc chay code
     * @param string $message thong bao loi
     * 
     * @return void
     */ 
    
    protected function reportError($message=null){
        $m = is_null($message) ? $this->errorMessage : $message;
        switch($this->reportLevel){
            case 0:
                //nothing
            break;
            
            case 1:
                echo '<br />'.$m."<br />";
            break;
            
            case 2:
                // $this->pdo = null;
                throw new Exception($m);
            break;
            
            default:
                $this->pdo = null;
                die('<br />'.$m."<br />");
        }
    }
    
    /**
     * thiet lap thong bao loi
     * @param int $level
     * 
     * 
     * @return bool
     */ 

    public function setErrorReportingLevel($level = 0){
        if(is_int($level) && $level >= 0 && $level <= 3){
            $this->reportLevel = $level;
            return true;
        }
        return false;
    }
    
    /**
     * dua cac tham so ve mac dinh
     */ 
    
    public function reset(){
        $this->params = [];
        $this->paramKeys = [];
        $this->stmt = null;
    }
    
    
    
    
    /**
     * thuc thi query
     * @param string
     * 
     * @return PDOStatement
     */ 
    
    public function query($query){
        $rs = 0;
        try{
            $this->stmt = $this->pdo->query($query);
            return $this->stmt;
        }catch(PDOException $e){
            $msg = $e->getMessage();
            $this->errorMessage = $msg;
            $this->reportError();
        }catch(Exception $e){
            $msg = $e->getMessage();
            $this->errorMessage = $msg;
            $this->reportError();
        }
        return $rs;
    }
    /**
     * thuc thi query
     * @param string
     * 
     * @return int
     */ 
    
    public function exec($query){
        $rs = null;
        try{
            return $this->pdo->exec($query);
        }catch(PDOException $e){
            $msg = $e->getMessage();
            $this->errorMessage = $msg;
            $this->reportError();
        }catch(Exception $e){
            $msg = $e->getMessage();
            $this->errorNessage = $msg;
            $this->reportError();
        }
        return $rs;
    }
    
    /**
     * ham thuc thi chuoi truy van voi tham so truyen vao
     * @param string $query        Chuoi truy van
     * @param array $params        Tham so
     * 
     * @return PDOStatement
     */ 
    
    public function execute($query, $params = []){
        $stm = null;
        try{
            // Select * from users Where username = 'doanln'
            // Select * from users Where username = :username
            // [':username' => 'doanln']
            $stmt = $this->pdo->prepare($query);
            if($stmt->execute($params)){
                $this->stmt = $stmt;
                $stm = $stmt;
            }
        }catch(PDOException $e){
            $msg = $e->getMessage();
            $this->error_message = $msg;
            $this->reportError();
        }
        
        return $stm;
    }
    
    
    public function getPDO(){
        return $this->pdo;
    }
    
    public function getStmt(){
        return $this->stmt;
    }
    


    /**
     * mô tả 1 bảng
     * 
     * @param string $table
     * 
     * @return array 
     * 
     */
    public function describeTable($table = '')
    {
        $fields = array();
        $types = array();
        $primary = '';
        $tbs = explode(' ', trim($table));
        $ftb = $tbs[0];
        $stmt = $this->query("DESCRIBE $table");
        if(!$stmt) return null;
        if($tableFields = $stmt->fetchAll(PDO::FETCH_ASSOC)){
            foreach($tableFields as $column){
                $f = $column['Field'];
                if($column['Key']=='PRI'){
                    $primary = $f;
                }
                $fields[] = $f;
                $b = explode('(', $column['Type']);
                $types[$f] = $b[0];
            }
        }
        return compact('fields','primary','types');
    }
    
}

?>