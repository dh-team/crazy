<?php
namespace App\Controllers;

class DemoFormController extends Controller{
    //
    public function getForm()
    {
        return $this->view('form');
    }
}