<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Widget;

abstract class BaseWidget
{
    abstract public function getData($request, $params = []);

    public function saveData($contentId,$paramsOld, $params = [])
    {
        
    }
}
