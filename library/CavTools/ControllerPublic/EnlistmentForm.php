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
    
    public static function actionCreateThread($rrdBotID, $botUsername, $forumID, $title, $message)
    {
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $writer->set('user_id', $rrdBotID);
        $writer->set('username', $botUsername);
        $writer->set('title', $title);
        $postWriter = $writer->getFirstMessageDw();
        $postWriter->set('message', $message);
        $writer->set('node_id', $forumID);
        $writer->preSave();
        $writer->save();
        return $writer->getMergedData();
    }

    public static function actionWrite($userID, $recruiter, $lastName, $firstName, $age,
                                    $timezone, $date, $steamID, $inClan, $pastClans,
                                    $game, $reenlistment)
    {
        $dwEnlistment = XenForo_DataWriter::create('CavTools_DataWriter_Enlistments');
        $dwEnlistment->set('user_id', $userID);
        $dwEnlistment->set('recruiter', $recruiter);
        $dwEnlistment->set('last_name', $lastName);
        $dwEnlistment->set('first_name', $firstName);
        $dwEnlistment->set('age', $age);
        $dwEnlistment->set('timezone', $timezone);
        $dwEnlistment->set('enlistment_date', $date);
        $dwEnlistment->set('steamID', $steamID);
        $dwEnlistment->set('in_clan', $inClan);
        if ($inClan == true) {
            $dwEnlistment->set('past_clans', $pastClans);
        }
        $dwEnlistment->set('game', $game);
        $dwEnlistment->set('reenlistment', $reenlistment);
        $dwEnlistment->save();
    }

    public function actionPost()
    {

        //Action can only be called via post
        $this->_assertPostOnly();

        $visitor  = XenForo_Visitor::getInstance()->toArray();
        $userID   = $visitor['user_id'];
        $recruiter  = $this->_input->filterSingle('recruiter', XenForo_Input::STRING);
        $lastName  = $this->_input->filterSingle('last_name', XenForo_Input::STRING);
        $firstName = $this->_input->filterSingle('first_name', XenForo_Input::STRING);
        $age       = $this->_input->filterSingle('age', XenForo_Input::STRING);
        $timezone  = $this->_input->filterSingle('timezone', XenForo_Input::STRING);
        $date      = date("m.d.y");;
        $steamID   = $this->_input->filterSingle('steamID', XenForo_Input::STRING);
        $inClan      = $this->_input->filterSingle('clan', XenForo_Input::BOOLEAN);
        $pastClans = $this->_input->filterSingle('past_clans', XenForo_Input::STRING);
        $game      = $this->_input->filterSingle('game', XenForo_Input::STRING);
        $reenlistment  = $this->_input->filterSingle('reenlistment', XenForo_Input::BOOLEAN);

        $this->actionWrite($userID, $recruiter, $lastName, $firstName, $age,
            $timezone, $date, $steamID, $inClan, $pastClans,
            $game, $reenlistment);

        //Get values from options
        $rrdBotID = XenForo_Application::get('options')->enlistmentPosterID;
        $forumID  = XenForo_Application::get('options')->enlistmentForumID;

        $db = XenForo_Application::get('db');
        $botUsername = $db->fetchRow("
                            SELECT username
                            FROM xf_users 
                            WHERE user_id = " . $rrdBotID . "
                        ");

        $title   = '';
        $message = '';


        $this->actionCreateThread($rrdBotID, $botUsername, $forumID, $title, $message);


        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $this->getDynamicRedirect()
        );
    }

    public function enlistRalta()
    {
        // TODO - write hidden pref to recruit ralta if recruiter = S6
    }

}