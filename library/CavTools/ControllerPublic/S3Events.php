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

        $eventList = $db->fetchAll("
            SELECT id, name, date, prereq, description, instructor
            FROM xf_ct_s3_events
             ORDER BY id ASC
        ");

        // $eventList = $this->_getS3EventModel();
        $today = getdate();

        foreach ($eventList as $event)
        {
            if (XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'sendAWOLPM')) {
                $canRemoveEvent = true;
                if ($event['date'] >= $today['0'])
                {
                    $row .= "<tr><td><b>" . $event['date'] . "</b></td><td>" . $event['name'] . "</td><td>" . date('dMy', $event['date']) . "</td><td>" . $event['prereq'] . "</td><td>" . $event['instructor'] . "</td><td><input type=\"radio\" name=\"event[]\" value=" . $event['id'] . "></td></tr>" . PHP_EOL;
                }
            } else {
                $canRemoveEvent = false;
                if ($event['date'] >= $today['0'])
                {
                    $row .= "<tr><td><b>" . $event['date'] . "</b></td><td>" . $event['name'] . "</td><td>" . date('dMy', $event['date']) . "</td><td>" . $event['prereq'] . "</td><td>" . $event['instructor'] . "</td></tr>" . PHP_EOL;
                }
            }
        }

        //View Parameters
        $viewParams = array(
            'row' => $row,
            'canEditEvent' => $canRemoveEvent
        );
    }

    public function actionPost()
    {
        //Action can only be called via post
        $this->_assertPostOnly();

        // get user values
        $event = $_POST;

        // Datawriter to remove row from table
    }
} 