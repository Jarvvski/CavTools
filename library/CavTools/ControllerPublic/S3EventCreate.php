<?php

class CavTools_ControllerPublic_S3EventCreate extends XenForo_ControllerPublic_Abstract
{
    public function actionIndex()
    {
        //Get values from options
        $enable = XenForo_Application::get('options')->enableS3Events;

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'S3EventsView'))
        {
            throw $this->getNoPermissionResponseException();
        }

        //Set Time Zone to UTC
        date_default_timezone_set("UTC");

        //Get DB
        $db = XenForo_Application::get('db');

        //View Parameters
        $viewParams = array(
            'games' => $games
        );
    }
}