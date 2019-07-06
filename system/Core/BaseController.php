<?php
namespace System\Core;

abstract class BaseController 
{
    
    /**
     * đổ data ra view
     */
    public function view($viewName, array $data = [])
    {
        echo $viewName . ': '. json_encode($data);
    }
}
