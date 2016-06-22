<?php

class CavTools_Route_Prefix_S3Class implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        return $router->getRouteMatch('CavTools_ControllerPublic_S3Classes', $routePath, 'forums');
    }
}
