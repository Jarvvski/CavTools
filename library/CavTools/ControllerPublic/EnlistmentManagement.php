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

        $normalEnlistments = '';
        $reEnlistments = '';
        $userUrl = '/members/';

        $enlistments = array('CavTools' => $this->_getEnlistmentModel()->getAllEnlistment());

        if (count($enlistments) != 0) {
            foreach ($enlistments as $enlistment) {

                $cavName  = $enlistment['last_name'];
                $cavName .= '.';
                $cavName .= substr($enlistment['first_name'], 0, 1);

                if ($enlistment['enlistment_type'] = false) {
                    $normalEnlistments .= "<tr><td><a href=" . $userUrl . $enlistment['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $cavName . "</td><td>" . $enlistment['enlistment_date'] . "</td><td>" . $enlistment['age'] . "</td><td>" . $enlistment['steamID'] . "</td><td>" . $enlistment['clan'] . "</td><td>" . $enlistment['orders'] . "</td><td>" . $enlistment['game'] . "</td><td><input type=\"checkbox\" name=\"enlistments[]\" value=" . $enlistment['enlistment_id'] . "></td></tr>" . PHP_EOL;
                } else {
                    $reEnlistments .= "<tr><td><a href=" . $userUrl . $enlistment['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $cavName . "</td><td>" . $enlistment['enlistment_date'] . "</td><td>" . $enlistment['age'] . "</td><td>" . $enlistment['steamID'] . "</td><td>" . $enlistment['clan'] . "</td><td>" . $enlistment['orders'] . "</td><td>" . $enlistment['game'] . "</td><td><input type=\"checkbox\" name=\"enlistments[]\" value=" . $enlistment['enlistment_id'] . "></td></tr>" . PHP_EOL;
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


}