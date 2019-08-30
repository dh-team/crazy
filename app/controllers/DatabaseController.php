<?php
namespace App\Controllers;

use System\Http\Request;
use System\Database\DB;
use PDO;

class DatabaseController extends Controller{
    public function test(Request $request)
    {
        if($conn = DB::getConnection()){
            $stmt = $conn->execute("select * from users Where username = :username", [
                ':username' => $request->username
            ]);
            echo '<pre>';
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
                print_r($value);
            }
        }
        return "Hello World! I'm Test DB controller";
    }
}