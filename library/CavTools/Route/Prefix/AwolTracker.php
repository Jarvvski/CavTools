<?php

class CavTools_Route_Prefix_AwolTracker implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		return $router->getRouteMatch('CavTools_ControllerPublic_AwolTracker', $routePath, 'forums');
	}
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'thread_id');
	}
}
