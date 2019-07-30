<?php
namespace App\Controllers;

use System\Http\Request;

class DemoFormController extends Controller{
    //
    public function getForm()
    {
        return $this->view('form');
    }

    /**
     * lấy thông tin để thực hiện login
     */
    public function login()
    {
        $request = new Request();
        // php thuần 
        
        print_r($request->all());
        
    }
}