<?php

class CavTools_ControllerPublic_EnlistmentForm extends XenForo_ControllerPublic_Abstract {

    public function actionIndex() {

        //Get values from options
        $enable = XenForo_Application::get('options')->enableEnlistmentForm;

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'EnlistmentForm'))
        {
            throw $this->getNoPermissionResponseException();
        }

        //Set Time Zone to UTC
        date_default_timezone_set("UTC");


        //View Parameters
        $viewParams = array(
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_EnlistmentForm', 'CavTools_enlistmentForm', $viewParams);
    }
    
    public function actionCreateThread(array $user, $forumID, $title, $message)
    {
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $writer->set('user_id', $user['user_id']);
        $writer->set('username', $user['username']);
        $writer->set('title', $title);
        $postWriter = $writer->getFirstMessageDw();
        $postWriter->set('message', $message);
        $writer->set('node_id', $forumID);
        $writer->preSave();
        $writer->save();
        return $writer->getMergedData();
    }

    public function actionWrite($userID, $username, $lastName, $firstName,
                                $age, $steamID, $clan, $orders, $game, $type, $date)
    {
        $dwEnlistment = XenForo_DataWriter::create('CavTools_DataWriter_Enlistments');
        $dwEnlistment->set('user_id', $userID);
        $dwEnlistment->set('username', $username);
        $dwEnlistment->set('last_name', $lastName);
        $dwEnlistment->set('first_name', $firstName);
        $dwEnlistment->set('age', $age);
        $dwEnlistment->set('steamID', $steamID);
        $dwEnlistment->set('clan', $clan);
        $dwEnlistment->set('orders', $orders);
        $dwEnlistment->set('game', $game);
        $dwEnlistment->set('enlistment_type', $type);
        $dwEnlistment->set('enlistment_date', $date);
        $dwEnlistment->save();
    }

    public function actionPost()
    {

        //Action can only be called via post
        $this->_assertPostOnly();

        $visitor  = XenForo_Visitor::getInstance()->toArray();
        $userID   = $visitor['user_id'];
        $username = $visitor['username'];

        $lastName  = $this->_input->filterSingle('last_name', XenForo_Input::STRING);
        $firstName = $this->_input->filterSingle('first_name', XenForo_Input::STRING);
        $age       = $this->_input->filterSingle('age', XenForo_Input::STRING);
        $steamID   = $this->_input->filterSingle('steamID', XenForo_Input::STRING);
        $clan      = $this->_input->filterSingle('clan', XenForo_Input::BOOLEAN);
        $orders    = $this->_input->filterSingle('clan', XenForo_Input::BOOLEAN);
        $game      = $this->_input->filterSingle('game', XenForo_Input::STRING);
        $type      = $this->_input->filterSingle('enlistmentType', XenForo_Input::BOOLEAN);
        $date      = date("m.d.y");

        $this->actionWrite($userID, $username, $lastName, $firstName,
            $age, $steamID, $clan, $orders, $game, $type, $date);

        //Get values from options
        $rrdBotID = XenForo_Application::get('options')->enlistmentPosterID;
        $forumID  = XenForo_Application::get('options')->enlistmentForumID;

        $botUsername = $db->fetchRow("
                            SELECT username
                            FROM xf_users 
                            WHERE user_id = " . $rrdBotID . "
                        ");

        $title   = ;
        $message = ;


        $this->actionCreateThread($rrdBotID, $botUsername, $forumID, $title, $message);


        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->getDynamicRedirect()
        );
    }
}