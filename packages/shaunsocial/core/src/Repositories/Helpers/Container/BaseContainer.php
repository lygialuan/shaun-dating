<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Container;

abstract class BaseContainer
{
    abstract public function getData($params = []);

    public function getRequest()
    {
        return request();
    }

    public function saveData($contentId,$paramsOld, $params = [])
    {
        
    }
}
