<?php

class CavTools_ControllerPublic_S3EventCreate extends XenForo_ControllerPublic_Abstract
{
    public function getS3Bot()
    {
        //Get values from options
        $userID = XenForo_Application::get('options')->s3PosterID;
        $db = XenForo_Application::get('db');
        $botUsername = $db->fetchRow("
                            SELECT username
                           FROM xf_user
                           WHERE user_id = " . $userID . "
                       ");
        $username = $botUsername['username'];
        $botVars = array("user_id", "username");
        return compact($botVars);
    }

    public function actionIndex()
    {
        //Get values from options
        $enable = XenForo_Application::get('options')->enableS3Events;
        $games = XenForo_Application::get('options')->games;

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'S3EventCreate'))
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
    
    public function actionPost()
    {

        // Action can only be called via post
        $this->_assertPostOnly();

        // Get poster info
        $visitor = XenForo_Visitor::getInstance()->toArray();
        
        // Get form values
        $type = $this->_input->filterSingle('type', XenForo_Input::STRING);
        $customTitle = $this->_input->filterSingle('title', XenForo_Input::STRING);
        $className = $this->_input->filterSingle('class', XenForo_Input::STRING);
        $date = $this->_input->filterSingle('date', XenForo_Input::STRING);
        $game = $this->_input->filterSingle('game', XenForo_Input::STRING);
        $text = $this->_input->filterSingle('text', XenForo_Input::STRING);

        $operation = false;
        $class = false;

        if ($type == "Operation") {
            $operation = true;
        } else if ($type == "Class") {
            $class = true;
        }

        // Get values from options
        $classForumID  = XenForo_Application::get('options')->s3ClassForumID;
        $operationForumID  = XenForo_Application::get('options')->s3OperationForumID;

        $eventType = 0;

        if ($operation) {
            $forumID = $operationForumID;
            $eventType = 1;
            $eventTitle = $customTitle;
        } else if ($class) {
            $forumID = $classForumID;
            $eventType = 2;
            $eventTitle = $className;
        }

        $title = $this->createThreadTitle($eventTitle, $eventType, $game);
        $message = $this->createThreadContent($text, $eventType, $className, $visitor);
        $threadID = $this->createThread($forumID, $title, $message);

        $this->createData($eventType, $title, $date, $game, $message, $visitor, $threadID);

        // redirect after post
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('threads', array('thread_id' => $threadID)),
            new XenForo_Phrase('event_created')
        );
    }

    public function createThreadTitle($eventTitle, $eventType, $game)
    {
        $title = "";
        switch ($eventType)
        {
            case 1: $title .= "[Operation]"; break;
            case 2: $title .= "[Class]"; break;
            case 0: $title .= "[Unknown Type]";
        }
        return $title .= " " . $eventTitle . " " . $game;
    }

    public function createThreadContent($text, $eventType, $className, $visitor)
    {
        $message = "";
        $classText = "";
        $newLine = "\n";
        $classNames = XenForo_Application::get('options')->s3ClassNames;
        $home = XenForo_Application::get('options')->homeURL;
        $classNames = explode(',', $classNames);
        $s3ClassModel = $this->_getS3ClassModel();
        

        foreach ($classNames as $class)
        {
            if ($className == $class)
            {
                $classQuery = $s3ClassModel->getClassByClassName($className);
                $classText = $classQuery['class_text'];
            }
        }
        switch ($eventType)
        {
            case 1: $message .= $text; break;
            case 2: $message = $classText;
        }

        $submittedURL = '[URL="http://' .$home.'/members/'.$visitor['user_id'].'"]'.$visitor['username'].'[/URL]';
        $submittedBy = '[Size=3][I]Submitted by - ' . $submittedURL . '[/I][/Size]';
        
        return $message .= $newLine . $submittedBy;
    }
    
    public function createThread($forumID, $title, $message)
    {
        // get rrd bot values
        $poster = $this->getS3Bot();
        // write the thread
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $writer->set('user_id', $poster['user_id']);
        $writer->set('username', $poster['username']);
        $writer->set('title', $title);
        $postWriter = $writer->getFirstMessageDw();
        $postWriter->set('message', $message);
        $writer->set('node_id', $forumID);
        $writer->preSave();
        $writer->save();
        return $writer->getDiscussionId();
    }
    
    public function createData($type, $title, $date, $game, $text, $visitor, $threadID)
    {
        $dw = XenForo_DataWriter::create('CavTools_DataWriter_S3Event');
        $dw->set('event_type', $type);
        $dw->set('event_title', $title);
        $dw->set('event_date', $date);
        $dw->set('event_game', $game);
        $dw->set('event_text', $text);
        $dw->set('username', $visitor['username']);
        $dw->set('thread_id', $threadID);
        $dw->save();
    }

    protected function _getS3EventModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_S3Event' );
    }
}