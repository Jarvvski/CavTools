<?php

class CavTools_ControllerPublic_AwolTracker extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		//Get values from options
		$enable = XenForo_Application::get('options')->enableAwolTracker;

		if(!$enable) {
			throw $this->getNoPermissionResponseException();
		}

		if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'awoltrackerView'))
		{
			throw $this->getNoPermissionResponseException();
		}

		//Set Time Zone to UTC
		date_default_timezone_set("UTC");

		//Get DB
		$db = XenForo_Application::get('db');

		//query
		$members = $db->fetchAll("
			SELECT user_id, username, secondary_group_ids
			FROM xf_user
			ORDER BY username ASC
		");




		//Get values from options
		$milpacsBoolean		= XenForo_Application::get('options')->awolMilpacsBoolean;
		$daysTillInt 		= XenForo_Application::get('options')->awolDaysTill;
		$checkIds 			= XenForo_Application::get('options')->awolCheckIDs;
		$voidIds			= XenForo_Application::get('options')->awolVoidIDs;
		$ssIds 				= XenForo_Application::get('options')->awolSSIDs;
		$firstBnIds			= XenForo_Application::get('options')->awolFirstBattIDs;
		$secondBnIds 		= XenForo_Application::get('options')->awolSecondBattIDs;

		//Explode options from strings into arrays
		$checkIds		= explode(',', $checkIds);
		$voidIds 		= explode(',', $voidIds);
		$ssIds 			= explode(',', $ssIds);
		$firstBnIds		= explode(',', $firstBnIds);
		$secondBnIds 	= explode(',', $secondBnIds);

		//Define Variables
		$ssMemberList = '';
		$firstBnMemberList = '';
		$secondBnMemberList = '';
		$unsortedMemberList = '';
		$awolTime = ($daysTillInt * 86400);
		$userUrl = '/members/';

		//Renumber Array
		$members = array_values($members);

		//Output
		foreach($members as $member){

			//Convert secondary group id column to array
			$memberIDs = $member['secondary_group_ids'];
			$memberIDs = explode(',', $memberIDs);
			//Get Member ID Variable
			$memberID = $member['user_id'];

			//Get member's latest post
			$memberLastPostQuery = $db->fetchRow("
				SELECT MAX(post_date)
				FROM xf_post
				WHERE user_id = " . $memberID . "
			");

			//Define Variables
			$memberLastPost = implode("",$memberLastPostQuery);
			$sinceLastPost = (time() - $memberLastPost);
			$daysAwol = round(($sinceLastPost - $awolTime) / 86400);
            $canSendPM = false;

            if (XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'sendAWOLPM')) {
                $canSendPM = true;
                //Optional Milpacs Integration
                if ($milpacsBoolean == 1) {

                    if ((count(array_intersect($checkIds, $memberIDs)) != 0) AND (count(array_intersect($voidIds, $memberIDs)) == 0) AND ($sinceLastPost > $awolTime) AND ($memberLastPost != "")) {

                        //Get Milpacs Position ID
                        $position = $db->fetchRow("
                            SELECT t1.position_id, t1.user_id, t2.position_title
                            FROM xf_pe_roster_user_relation t1
                            INNER JOIN xf_pe_roster_position t2
                            ON t1.position_id = t2.position_id
                            WHERE user_id = " . $member['user_id'] . "
                        ");

                        if (count(array_intersect($firstBnIds, $memberIDs)) != 0) {

                            $firstBnMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position['position_title'] . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td><td><input type=\"checkbox\" name=\"users[]\" value=" . $member['user_id'] . "></td></tr>" . PHP_EOL;

                        } elseif (count(array_intersect($secondBnIds, $memberIDs)) != 0) {

                            $secondBnMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position['position_title'] . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td><td><input type=\"checkbox\" name=\"users[]\" value=" . $member['user_id'] . "></td></tr>" . PHP_EOL;

                        } elseif (count(array_intersect($ssIds, $memberIDs)) != 0) {

                            $ssMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position['position_title'] . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td><td><input type=\"checkbox\" name=\"users[]\" value=" . $member['user_id'] . "></td></tr>" . PHP_EOL;

                        } else {

                            $unsortedMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position['position_title'] . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td><td><input type=\"checkbox\" name=\"users[]\" value=" . $member['user_id'] . "></td></tr>" . PHP_EOL;

                        }
                    }
                } else {

                    if ((count(array_intersect($checkIds, $memberIDs)) != 0) AND (count(array_intersect($voidIds, $memberIDs)) == 0) AND ($sinceLastPost > $awolTime) AND ($memberLastPost != "")) {

                        if (count(array_intersect($firstBnIds, $memberIDs)) != 0) {

                            $firstBnMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td><td><input type=\"checkbox\" name=\"users[]\" value=" . $member['user_id'] . "></td></tr>" . PHP_EOL;

                        } elseif (count(array_intersect($secondBnIds, $memberIDs)) != 0) {

                            $secondBnMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td><td><input type=\"checkbox\" name=\"users[]\" value=" . $member['user_id'] . "></td></tr>" . PHP_EOL;

                        } elseif (count(array_intersect($ssIds, $memberIDs)) != 0) {

                            $ssMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td><td><input type=\"checkbox\" name=\"users[]\" value=" . $member['user_id'] . "></td></tr>" . PHP_EOL;

                        } else {

                            $unsortedMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td><td><input type=\"checkbox\" name=\"users[]\" value=" . $member['user_id'] . "></td></tr>" . PHP_EOL;

                        }
                    }
                }
            } else {

                //Optional Milpacs Integration
                if ($milpacsBoolean == 1) {

                    if ((count(array_intersect($checkIds, $memberIDs)) != 0) AND (count(array_intersect($voidIds, $memberIDs)) == 0) AND ($sinceLastPost > $awolTime) AND ($memberLastPost != "")) {

                        //Get Milpacs Position ID
                        $position = $db->fetchRow("
                            SELECT t1.position_id, t1.user_id, t2.position_title
                            FROM xf_pe_roster_user_relation t1
                            INNER JOIN xf_pe_roster_position t2
                            ON t1.position_id = t2.position_id
                            WHERE user_id = " . $member['user_id'] . "
                        ");

                        if (count(array_intersect($firstBnIds, $memberIDs)) != 0) {

                            $firstBnMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position['position_title'] . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td></tr>" . PHP_EOL;

                        } elseif (count(array_intersect($secondBnIds, $memberIDs)) != 0) {

                            $secondBnMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position['position_title'] . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td></tr>" . PHP_EOL;

                        } elseif (count(array_intersect($ssIds, $memberIDs)) != 0) {

                            $ssMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position['position_title'] . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td></tr>" . PHP_EOL;

                        } else {

                            $unsortedMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position['position_title'] . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td></tr>" . PHP_EOL;

                        }
                    }
                } else {

                    if ((count(array_intersect($checkIds, $memberIDs)) != 0) AND (count(array_intersect($voidIds, $memberIDs)) == 0) AND ($sinceLastPost > $awolTime) AND ($memberLastPost != "")) {

                        if (count(array_intersect($firstBnIds, $memberIDs)) != 0) {

                            $firstBnMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td></tr>" . PHP_EOL;

                        } elseif (count(array_intersect($secondBnIds, $memberIDs)) != 0) {

                            $secondBnMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td></tr>" . PHP_EOL;

                        } elseif (count(array_intersect($ssIds, $memberIDs)) != 0) {

                            $ssMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td></tr>" . PHP_EOL;

                        } else {

                            $unsortedMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td></tr>" . PHP_EOL;

                        }
                    }
                }

            }
		}



		//View Parameters
		$viewParams = array(
			'milpacsBoolean' => $milpacsBoolean,
			'ssMemberList' => $ssMemberList,
			'firstBnMemberList' => $firstBnMemberList,
			'secondBnMemberList' => $secondBnMemberList,
			'unsortedMemberList' => $unsortedMemberList,
            'canSendPM' => $canSendPM
		);

		//Send to template to display
		return $this->responseView('CavTools_ViewPublic_AwolTracker', 'CavTools_awoltracker', $viewParams);
	}
    
    public function actionSendPM() 
    {

        //Action can only be called via post
        $this->_assertPostOnly();

        // get user values
        $users = $_POST['users'];
        
        //Get values from options
        $messageText		= XenForo_Application::get('options')->awolPMText;
        $subject            = XenForo_Application::get('options')->awolPMSubject;


        foreach ($users as $user) {

            $message = $messageText;
            $sender = array('user_id' => $this->getUserID(), 'username' => $this->getUsername());
            $recipient = array($user);

            $this->createConversation($sender, $recipient, $subject, $message,
                $noInvites = true, $conversationClosed = true, $markReadForSender = true);
        }

        // redirect back to the normal scratchpad index page
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('forums'),
            new XenForo_Phrase('request_received')
        );

    }

    public function getUserID()
    {
        $visitor = XenForo_Visitor::getInstance()->toArray();
        return $visitor['user_id'];
    }

    public function getUsername()
    {
        $visitor = XenForo_Visitor::getInstance()->toArray();
        return $visitor['username'];
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
}
