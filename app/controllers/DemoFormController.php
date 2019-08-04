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
        
        print_r($request->all());
        
    }

    /**
     * lấy thông tin để thực hiện login
     */
    public function update(Request $request, int $id, $test = null)
    {
        
        return ($request->id);
        
    }
}