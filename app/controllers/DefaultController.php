<?php
namespace App\Controllers;

class DefaultController extends Controller{
    //
    public function info($name, $age, $gender)
    {
        return $this->view('info', compact('name', 'age', 'gender'));
    }
}