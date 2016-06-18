<?php

class CavTools_ControllerPublic_EnlistmentManagement extends XenForo_ControllerPublic_Abstract {

    public function actionIndex() {

        //Get values from options
        $enable = XenForo_Application::get('options')->enableEnlistmentFormManagement;

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'EnlistmentFormManagement'))
        {
            throw $this->getNoPermissionResponseException();
        }

        //Set Time Zone to UTC
        date_default_timezone_set("UTC");

        //Get DB
        $db = XenForo_Application::get('db');

        $normalEnlistments = " ";
        $reEnlistments = " ";
        $threadURL = '/threads/';
        $recruiter = " ";

        $enlistModel = $this->_getEnlistmentModel();
        $enlistments = $enlistModel->getAllEnlistment();
        $threadID = $enlistModel->getEnlistmentThreadID();

        if (count($enlistments) != 0) {
            foreach ($enlistments as $enlistment) {

                $name = $enlistment['first_name'] . $enlistment['last_name'];
                
                $thread = $threadURL . $threadID;

                if ($enlistment['recruiters'] != null)
                {
                    $recruiter = $enlistment['recruiters'];
                }

                if ($enlistment['reenlistment'] = false) {
                    $normalEnlistments .= "<tr><td><a href=" . $threadURL . $enlistment['en'] . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . $enlistment['enlistment_date'] . "</td><td>" . $name . "</td><td>" . $recruiter . "</td><td><input type=\"checkbox\" name=\"enlistments[]\" value=" . $enlistment['enlistment_id'] . "></td></tr>" . PHP_EOL;
                } else {
                    $reEnlistments .= "<tr><td><a href=" . $threadURL . $enlistment['en'] . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . $enlistment['enlistment_date'] . "</td><td>" . $name . "</td><td>" . $recruiter . "</td><td><input type=\"checkbox\" name=\"enlistments[]\" value=" . $enlistment['enlistment_id'] . "></td></tr>" . PHP_EOL;
                }
            }
        }

        //View Parameters
        $viewParams = array(
            'normalEnlistments' => $normalEnlistments,
            'reEnlistments' => $reEnlistments,
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_EnlistmentForm', 'CavTools_enlistmentForm', $viewParams);
    }

    public function actionPost()
    {
        // TODO - write function to do dennials, approvals, etc
    }

    public static function actionCreatePost($userID, $username, $threadId, $message, $state = 'visible')
    {
        $threadModel = XenForo_Model::create('XenForo_Model_Thread');
        $thread = $threadModel->getThreadById($threadId);
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
        $writer->set('user_id', $userID);
        $writer->set('username', $username);
        $writer->set('message', $message);
        $writer->set('message_state', $state);
        $writer->set('thread_id', $thread['thread_id']);
        $writer->save();
        $post = $writer->getMergedData();
        return $post;
    }

    public static function createConversation(array $sender, array $recipients, $subject, $message,
                                              $noInvites = false, $conversationClosed = false, $markReadForSender = true)
    {
        /** @var $conversationDw XenForo_DataWriter_ConversationMaster */
        $conversationDw = XenForo_DataWriter::create('XenForo_DataWriter_ConversationMaster');
        $conversationDw->set('user_id', $sender['user_id']);
        $conversationDw->set('username', $sender['username']);
        $conversationDw->set('title', $subject);
        if ($noInvites) {
            $conversationDw->set('open_invite', 0);
        }
        if ($conversationClosed) {
            $conversationDw->set('conversation_open', 0);
        }

        $conversationDw->addRecipientUserIds($recipients);
        $messageDw = $conversationDw->getFirstMessageDw();
        $messageDw->set('message', $message);
        $conversationDw->preSave();
        $conversationDw->save();
        $conversation = $conversationDw->getMergedData();
        /** @var $convModel XenForo_Model_Conversaiont */
        $convModel = XenForo_Model::create('XenForo_Model_Conversation');
        if ($markReadForSender) {
            $convModel->markConversationAsRead(
                $conversation['conversation_id'], $sender['user_id'], XenForo_Application::$time
            );
        }

        return $conversationDw->getMergedData();
    }
    
    public function vacBan()
    {
        // TODO - steam API check
        // http://api.steampowered.com/ISteamUser/GetPlayerBans/v1/?key=084E3337CA952D16B3810AA53629B262&steamids=76561197977479862
    }

    public function checkName()
    {
        // TODO - query DB for name in milpacs
    }


    
    


}