<?php
namespace System\Core;

abstract class BaseController 
{
    
    /**
     * đổ data ra view
     */
    public function view($viewName, array $data = [])
    {
        return View::make($viewName, $data)->render();
    }
}
