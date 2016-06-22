<?php

class CavTools_ControllerPublic_EnlistmentManagement extends XenForo_ControllerPublic_Abstract {

    public function actionIndex() {

        //Get values from options
        $enable = XenForo_Application::get('options')->enableEnlistmentManagement;

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'EnlistmentManagement'))
        {
            throw $this->getNoPermissionResponseException();
        }

        if (XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'canSubmitEnlistment'))
        {
            $canAction = true;
        } else {
            $canAction = false;
        }

        //Set Time Zone to UTC
        date_default_timezone_set("UTC");

        //Get DB
        $db = XenForo_Application::get('db');

        $normalEnlistments = " ";
        $reEnlistments = " ";
        $threadURL = '/threads/';

        $enlistModel = $this->_getEnlistmentModel();
        $enlistments = $enlistModel->getAllEnlistment();

        if (count($enlistments) != 0) {
            foreach ($enlistments as $enlistment) {
                
                $thread = $threadURL . $enlistment['thread_id'];
                $underage = $enlistment['under_age'];
                $reenlistment = $enlistment['reenlistment'];
                $daysSince = "" . round((time() - $enlistment['last_update']) / 86400) . " day(s)";
               
                // capitalise the first letters of the first and last name, even if uppercase already
                $firstName = ucwords($enlistment['first_name']);
                $lastName = ucwords($enlistment['last_name']);
                $cavName = "";
                $cavName = $lastName . "." . $firstName[0];
                $nameCheck = $this->checkName($cavName);
                
                switch ($enlistment['current_status'])
                {
                    case 1: $status = '<td style="color:red;">Denied</td>';break;
                    case 2: $status = '<td style="color:green;">Approved</td>';break;
                    case 3: $status = '<td style="color:yellow;">Open</td>';break;
                }
                
                
                if ($daysSince > 7) {
                    $dayStatus = '<td style="color:red;">';
                } else {
                    $dayStatus = '<td>';
                }

                switch($nameCheck)
                {
                    case 1: $nameStatus = '<td style="color:red;">';break;
                    case 2: $nameStatus = '<td style="color:green;">';break;
                }
                
                switch ($enlistment['vac_ban']) {
                    case 1: $banStatus = '<td style="color:red;">';break;
                    case 2: $banStatus = '<td style="color:green;">';break;
                    case 3: $banStatus = '<td style="color:yellow;">';break;
                }

                if ($underage) {
                    $ageStatus = '<td style="color:red;">';
                } else {
                    $ageStatus = '<td>';
                }
                if ($canAction) {
                    if (!$reenlistment) {
                        if (count($enlistments) != 0) {
                            $normalEnlistments .= "<tr><td><a href=" . $thread . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . date('m-d-y', $enlistment['enlistment_date']) . "</td>$dayStatus" . $daysSince . "</td>$nameStatus" . htmlspecialchars($cavName) . "</td><td>". htmlspecialchars($enlistment['first_name']) . "</td><td>" . htmlspecialchars($enlistment['recruiter']) . "</td>$banStatus " . htmlspecialchars($enlistment['steamID']) . "</td>$ageStatus" . htmlspecialchars($enlistment['age']) . "</td>$status<td><input type=\"checkbox\" name=\"enlistments[]\" value=" . $enlistment['enlistment_id'] . "></td></tr>" . PHP_EOL;
                        }
                    }

                    if ($reenlistment) {
                        if (count($enlistments) != 0) {
                            $reEnlistments .= "<tr><td><a href=" . $thread . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . date('m-d-y', $enlistment['enlistment_date']) . "</td>$dayStatus" . $daysSince . "</td>$nameStatus" . htmlspecialchars($cavName) . "</td><td>". htmlspecialchars($enlistment['first_name']) . "</td><td>" . htmlspecialchars($enlistment['recruiter']) . "</td>$banStatus" . htmlspecialchars($enlistment['steamID']) . "</td>$ageStatus" . htmlspecialchars($enlistment['age']) . "</td>$status<td><input type=\"checkbox\" name=\"enlistments[]\" value=" . $enlistment['enlistment_id'] . "></td></tr>" . PHP_EOL;
                        }
                    }
                } else {
                    if (!$reenlistment) {
                        if (count($enlistments) != 0) {
                            $normalEnlistments .= "<tr><td><a href=" . $thread . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . date('m-d-y', $enlistment['enlistment_date']) . "</td>$dayStatus" . $daysSince . "</td>$nameStatus" . htmlspecialchars($cavName) . "</td><td>". htmlspecialchars($enlistment['first_name']) . "</td><td>" . htmlspecialchars($enlistment['recruiter']) . "</td>$banStatus " . htmlspecialchars($enlistment['steamID']) . "</td>$ageStatus" . htmlspecialchars($enlistment['age']) . "</td>$status</tr>" . PHP_EOL;
                        }
                    }

                    if ($reenlistment) {
                        if (count($enlistments) != 0) {
                            $reEnlistments .= "<tr><td><a href=" . $thread . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . date('m-d-y', $enlistment['enlistment_date']) . "</td>$dayStatus" . $daysSince . "</td>$nameStatus" . htmlspecialchars($cavName) . "</td><td>". htmlspecialchars($enlistment['first_name']) . "</td><td>" . htmlspecialchars($enlistment['recruiter']) . "</td>$banStatus" . htmlspecialchars($enlistment['steamID']) . "</td>$ageStatus" . htmlspecialchars($enlistment['age']) . "</td>$status</tr>" . PHP_EOL;
                        }
                    }
                }
            }
        }

        //View Parameters
        $viewParams = array(
            'normalEnlistments' => $normalEnlistments,
            'reEnlistments' => $reEnlistments,
            'canAction' => $canAction,
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_EnlistmentManagement', 'CavTools_EnlistmentManagement', $viewParams);
    }

    public function actionPost()
    {

        // get the user_id from the user
        $visitor  = XenForo_Visitor::getInstance()->toArray();
        $userID   = $visitor['user_id'];

        // get form values
        $rrdOption = $this->_input->filterSingle('rrd_option', XenForo_Input::STRING);
        $enlistments = $_POST['enlistments'];

        foreach($enlistments as $enlistmentID)
        {
            
            $enlistModel = $this->_getEnlistmentModel();
            $threadID = $enlistModel->getThreadID($enlistmentID);
            $currentStatus = $enlistModel->getEnlistmentStatus($enlistmentID);
            $message = "";
            
            switch($rrdOption)
            {
                case '1':
                    // Name Change - taken
                    $message = '[B]Please select a different name. Your name has been already taken.[/B]';
                    $action = 'Name Change - taken';
                    $this->writeLog($enlistmentID, $action);
                    break;
                case '2':
                    // Name Change - inappropriate
                    $message = '[B]Please select a different name. Your name is inappropriate.[/B]';
                    $action = 'Name Change - inappropriate';
                    $this->writeLog($enlistmentID, $action);
                    break;
                case '3':
                    // Steam ID
                    $message = '[B]Please provide your steam ID for checking.[/B]';
                    $action = 'Steam ID';
                    $this->writeLog($enlistmentID, $action);
                    break;
                case '4':
                    // Approved
                    $message = '[B]Application approved.[/B]';
                    $currentStatus = 2;
                    $this->updateEnlistmentsData($enlistmentID, $currentStatus);
                    $this->updateThread($threadID['thread_id'], $this->buildTitle($enlistmentID));
                    $action = 'Approved';
                    $this->writeLog($enlistmentID, $action);
                    break;
                case '5':
                    // Denied - Timed out
                    $message = '[B]Application timed out. Enlistment denied[/B]';
                    $currentStatus = 1;
                    $this->updateEnlistmentsData($enlistmentID, $currentStatus);
                    $this->updateThread($threadID['thread_id'], $this->buildTitle($enlistmentID));
                    $action = 'Denied - Timed out';
                    $this->writeLog($enlistmentID, $action);
                    break;
                case '6':
                    // Denied
                    $message = '[B]Enlistment denied.[/B]';
                    $currentStatus = 1;
                    $this->updateEnlistmentsData($enlistmentID, $currentStatus);
                    $this->updateThread($threadID['thread_id'], $this->buildTitle($enlistmentID));
                    $action = 'Denied';
                    $this->writeLog($enlistmentID, $action);
                    break;
                case '7':
                    // Moved
                    $message = '[B]Application sorted[/B]';
                    $this->sortApplication($enlistmentID, $threadID, $currentStatus['current_status']);
            }
            $this->createPost($threadID, $message);
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('enlistments')
        );
    }

    public function buildTitle($enlistmentID)
    {
        $enlistModel = $this->_getEnlistmentModel();
        $query = $enlistModel->getEnlistmentById($enlistmentID);
        $reenlistment = $query['reenlistment'];
        // capitalise the first letters of the first and last name, even if uppercase already
        $firstName = ucwords($query['first_name']);
        $lastName = ucwords($query['last_name']);
        $cavName = '';
        $cavName = $lastName . "." . $firstName[0];
        $game = $query['game'];
        $status = $query['current_status'];
        $title = "";

        if ($reenlistment)
        {
            $title = "[Re-Enlistment]";
        } else {
            $title = "[Enlistment]";
        }
        $title .= " - " . $cavName . " - " . $game;

        switch ($status)
        {
            case 1: $title .= " - DENIED"; break;
            case 2: $title .= " - APPROVED"; break;
        }

        return $title;
    }

    public function createPost($threadId, $message)
    {
        // write the first reply post
        $threadModel = XenForo_Model::create('XenForo_Model_Thread');
        $poster = $this->getRRDBot();
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
        $writer->set('user_id', $poster['userID']);
        $writer->set('username', $poster['username']);
        $writer->set('message', $message);
        $writer->set('message_state', 'visible');
        $writer->set('thread_id', $threadId['thread_id']);
        $writer->save();
    }

    public function updateThread($threadID, $title)
    {   
        // update the thread
        //Get values from options
        $dw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $dw->setExistingData($threadID);
        $dw->set('title', $title);
        $dw->save();
    }

    public function getRRDBot()
    {
        //Get values from options
        $userID = XenForo_Application::get('options')->enlistmentPosterID;

        $db = XenForo_Application::get('db');
        $botUsername = $db->fetchRow("
                            SELECT username
                           FROM xf_user
                           WHERE user_id = " . $userID . "
                       ");

        $username = $botUsername['username'];
        $botVars = array("userID", "username");
        return compact($botVars);
    }

    public function updateEnlistmentsData($enlistmentID, $currentStatus)
    {
        $dw = XenForo_DataWriter::create('CavTools_DataWriter_Enlistments');
        $dw->setExistingData($enlistmentID);
        $dw->set('current_status', $currentStatus);
        $dw->save();
    }

    public function writeLog($enlistmentID, $action)
    {
        $visitor  = XenForo_Visitor::getInstance()->toArray();

        $dw = XenForo_DataWriter::create('CavTools_DataWriter_EnlistmentLogs');
        $dw->set('enlistment_id',$enlistmentID);
        $dw->set('user_id', $visitor['user_id']);
        $dw->set('username', $visitor['username']);
        $dw->set('log_date', time());
        $dw->set('action_taken', $action);
        $dw->save();
    }

    public function checkName($cavName)
    {
        $enlistModel = $this->_getEnlistmentModel();
        $query = $enlistModel->checkNameDupe($cavName);

        $count = 0;
        //$queryType = gettype($query['username']);
        try {
            if ($cavName === $query[0]["username"] || $cavName === $query) {
                $count = 1;
            }
        } catch (Exception $e) {
            $count = 2;
        }
        return $count;
    }
    
    public function sortApplication($enlistmentID, $threadID, $status)
    {
        //Get values from options
        $completedAppID = XenForo_Application::get('options')->completedForumID;
        $deniedAppID = XenForo_Application::get('options')->deniedForumID;
        $forumID = '';

        switch ($status)
        {
            case 1:
                // Move to Denied
                $forumID = $deniedAppID;break;
            case 2:
                // Move to completed
                $forumID = $completedAppID;break;
        }

        $this->removeEnlistment($enlistmentID);
        $this->movethread($threadID,$forumID);
    }

    public function removeEnlistment($enlistmentID)
    {
        $dw = XenForo_DataWriter::create('CavTools_DataWriter_Enlistments');
        $dw->setExistingData($enlistmentID);
        $dw->set('hidden', true);
        $dw->save();
    }

    public function movethread($threadID, $forumID)
    {
        $dw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $dw->setExistingData($threadID);
        $dw->set('node_id', $forumID);
        $dw->set('discussion_open', '0');
        $dw->save();
    }

    protected function _getEnlistmentModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_Enlistment' );
    }
}