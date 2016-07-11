<?php

class CavTools_ControllerPublic_S3Events extends XenForo_ControllerPublic_Abstract {
    
    public function actionIndex() 
    {

        //Get values from options
        $enable = XenForo_Application::get('options')->enableS3Events;

        if(!$enable) 
        {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 's3EventsView'))
        {
            throw $this->getNoPermissionResponseException();
        }

        if (XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'canActionEvent'))
        {
            $canAction = true;
        } else {
            $canAction = false;
        }

        //Set Time Zone to UTC
        date_default_timezone_set("UTC");

        //Get DB
        $db = XenForo_Application::get('db');

        $model = $this->_getS3EventModel();
        $events = $model->getEventList();
        $threadURL = '/threads/';
        $memberURL = '/members/';
        $eventList = "";
        $this->checkDate($events);

        if(count($events) != 0) {
            foreach ($events as $event) {

                $thread = $threadURL. $event['thread_id'];
                $epoch = $event['event_time'];
                $eventDate = date('dMy', $event['event_date']);
                $zuluTime = date('Hi', $event['event_time']);
                $date = new DateTime("@$epoch");
                $date->setTimezone(new DateTimeZone('America/Chicago'));
                $cstTime = $date->format('Hi');
                $member = $memberURL . $event['user_id'];
                $poster = $event['username'];

                if ($canAction) {
                    $eventList .= "<tr><td><a href=" . $thread . "><b>" . $event['event_title'] . "</b></a></td><td>" . $eventDate . "</td><td>" . $zuluTime . "</td><td>" . $cstTime . "</td><td><a href=" . $member . "><b>" . $poster . "</b></a></td><td><input type=\"checkbox\" name=\"events[]\" value=" . $event['event_id'] . "></td></tr>" . PHP_EOL;
                } else {
                    $eventList .= "<tr><td><a href=" . $thread . "><b>" . $event['event_title'] . "</b></a></td><td>" . $eventDate . "</td><td>" . $zuluTime . "</td><td>" . $cstTime . "</td><td><a href=" . $member . "><b>" . $poster . "</b></a></td></tr>" . PHP_EOL;
                }
            }
        }

        //View Parameters
        $viewParams = array(
            'canAction' => $canAction,
            'eventList' => $eventList,
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_s3events', 'CavTools_S3Events', $viewParams);
    }

    public function actionPost()
    {
        //Action can only be called via post
        $this->_assertPostOnly();
        
        $events = $_POST['events'];
        
        foreach ($events as $event) {
            $this->setHidden($event);
        }

        // redirect after post
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('s3events'),
            new XenForo_Phrase('event_updated')
        );
    }

    public function checkDate($events)
    {
        foreach ($events as $event) {
            if ($event['event_date'] < time()) {
             $this->setHidden($event);
            }
        }
    }
    
    public function setHidden($event)
    {
        $dw = XenForo_DataWriter::create('CavTools_DataWriter_S3Event');
        $dw->setExistingData($event);
        $dw->set('hidden', 1);
        $dw->save();
    }
    

    protected function _getS3EventModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_S3Event' );
    }
} 