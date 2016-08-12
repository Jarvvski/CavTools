<?php

class CavTools_ControllerPublic_S3EventCreate extends XenForo_ControllerPublic_Abstract
{
    // Get our posting bot
    public function getS3Bot()
    {
        //Get values from options
        $userID = XenForo_Application::get('options')->s3PosterID;
        $db = XenForo_Application::get('db');
        $botUsername = $db->fetchRow("
                            SELECT username
                           FROM xf_user
                           WHERE user_id = '$userID'
                       ");
        $username = $botUsername['username'];
        $botVars = array('user_id' => $userID, 'username' => $username);
        return $botVars;
    }

    // on page load
    public function actionIndex()
    {
        //Get values from options
        $enable = XenForo_Application::get('options')->enableS3EventCreate;

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        // checks the perms of the user
        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'S3EventCreate'))
        {
            throw $this->getNoPermissionResponseException();
        }

        // Get the S3 model
        $model = $this->_getS3ClassModel();
        // Do model query
        $query = $model->getClassList();
        $classNames = array();

        foreach ($query as $item)
        {
            // Get class names from return query
            array_push($classNames, $item['class_name']);
        }

        // Games from options
        $games = XenForo_Application::get('options')->s3Games;
        // break string
        // game1,game2,game3
        $games = explode(',', $games);

        $timeOptions = $this->createTimeOptions();
        
        //Set Time Zone to UTC
        date_default_timezone_set("UTC");

        //Get DB
        $db = XenForo_Application::get('db');

        //View Parameters
        $viewParams = array(
            'timeOptions' => $timeOptions,
            'classNames' => $classNames,
            'games' => $games
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_CreateEvent', 'CavTools_S3EventCreation', $viewParams);
    }

    // Get time options 0000 - 2300
    public function createTimeOptions()
    {
        $timeOptions = array();
        for ($i=0;$i<24;$i++)
        {
            if ($i < 10) {
                // 06:00
                $timeValue ="0" . $i . "00";
            } else {
                // 14:00
                $timeValue = $i . "00";
            }
            // 00:00, 01:00
            array_push($timeOptions, $timeValue);   
        }
        return $timeOptions;
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
        $date = $this->_input->filterSingle('event_date', XenForo_Input::STRING);
        $time = $this->_input->filterSingle('event_time', XenForo_Input::STRING);
        $game = $this->_input->filterSingle('game', XenForo_Input::STRING);
        $text = $this->_input->filterSingle('text', XenForo_Input::STRING);

        $customTitle = htmlspecialchars($customTitle);
        $time = htmlspecialchars($time);
        $date = htmlspecialchars($date);

        $convertDate = new DateTime("$date");
        $date = $convertDate->format('U');

        $convertTime = new DateTime("$time");
        $time = $convertTime->format('U');

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
        $forumID = "";
        $eventTitle = "";

        if ($operation) {
            $forumID = $operationForumID;
            $eventType = 1;
            $eventTitle = $customTitle;
        } else if ($class) {
            $forumID = $classForumID;
            $eventType = 2;
            $eventTitle = $className;
        }

        $title = $this->createThreadTitle($eventTitle, $eventType, $game, $time, $date);
        $message = $this->createThreadContent($text, $eventType, $className, $visitor);
        $threadID = $this->createThread($forumID, $title, $message);

        // Thread now exists

        $this->createData($eventType, $title, $date, $time, $game, $message, $visitor, $threadID);
        $this->tweet($visitor, $title);

        // redirect after post
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('threads', array('thread_id' => $threadID)), // 7cav.us/threads/123
            new XenForo_Phrase('event_created')
        );
    }

    public function createThreadTitle($eventTitle, $eventType, $game, $time, $date)
    {
        $eventDate = date('dMy', $date); // 13Jun2016
        $zuluTime = date('Hi', $time);
        $title = "";
        switch ($eventType)
        {
            case 1: $title .= "[Operation]"; break;
            case 2: $title .= "[Class]"; break;
            case 0: $title .= "[Unknown Type]";
        }
        if ($eventType != 2) {
            return $title .= " " . $game . " " . $eventTitle . " " . $eventDate . " " . $zuluTime . "Z";
        } else {
            return $title .= " " . $eventTitle . " " . $eventDate . " " . $zuluTime . "Z";
        }
    }

    public function createThreadContent($text, $eventType, $className, $visitor)
    {
        $message = "";
        $classText = "";
        $newLine = "\n";
        $home = XenForo_Application::get('options')->homeURL; // 7cav.us
        $model = $this->_getS3ClassModel();
        $query = $model->getClassList();
        $classNames = array();

        foreach ($query as $item)
        {
            array_push($classNames, $item['class_name']);
        }
        

        foreach ($classNames as $class)
        {
            if ($className == $class)
            {
                $classQuery = $model->getClassByClassName($className);
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
        $writer->set('sticky', true);
        $writer->preSave();
        $writer->save();
        return $writer->getDiscussionId();
    }
    
    public function createData($type, $title, $date, $time, $game, $text, $visitor, $threadID)
    {
        $dw = XenForo_DataWriter::create('CavTools_DataWriter_S3Event');
        $dw->set('event_type', $type);
        $dw->set('event_title', $title);
        $dw->set('event_date', $date);
        $dw->set('event_time', $time);
        $dw->set('event_game', $game);
        $dw->set('event_text', $text);
        $dw->set('username', $visitor['username']);
        $dw->set('user_id', $visitor['user_id']);
        $dw->set('thread_id', $threadID);
        $dw->save();
    }

    protected function _getS3ClassModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_S3Class' );
    }

    public function tweet($visitor, $title)
    {
        $twitterModel = $this->_getTwitterBot();
        $text = $visitor['username'] . " just started " . $title;
        $hashtag = "#S3 #7Cav #IMO";
        $twitterModel->postStatus($text, $hashtag);
    }

    protected function _getTwitterBot()
    {
        return $this->getModelFromCache( 'CavTools_Model_IMOBot' );
    }
}