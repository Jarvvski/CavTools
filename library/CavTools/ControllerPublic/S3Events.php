<?php

class CavTools_ControllerPublic_S3Events extends XenForo_ControllerPublic_Abstract {
    
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

        // TODO - Show S3 Events 

        //View Parameters
        $viewParams = array(
        );
    }

    public function actionPost()
    {
        //Action can only be called via post
        $this->_assertPostOnly();
        
        // TODO - Remove S3 Event 
    }
} 