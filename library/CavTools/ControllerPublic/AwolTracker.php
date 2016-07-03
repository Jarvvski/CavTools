<?php

class CavTools_ControllerPublic_AwolTracker extends XenForo_ControllerPublic_Abstract
{

    public function getBot()
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
            $model = $this->_getAwolModel();

			//Get member's latest post
			$memberLastPost = $model->memberLastPost($memberID);

			//Define Variables
			$sinceLastPost = (time() - $memberLastPost);
			$daysAwol = round(($sinceLastPost - $awolTime) / 86400);
            $canSendPM = false;

            if (XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'sendAWOLPM')) {
                $canSendPM = true;
                //Optional Milpacs Integration
                if ($milpacsBoolean == 1) {

                    if ((count(array_intersect($checkIds, $memberIDs)) != 0) AND (count(array_intersect($voidIds, $memberIDs)) == 0) AND ($sinceLastPost > $awolTime) AND ($memberLastPost != "")) {

                        //Get Milpacs Position
                        $position = $model->milpacsPosition($memberID);

                        if (count(array_intersect($firstBnIds, $memberIDs)) != 0) {

                            $firstBnMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td><td><input type=\"checkbox\" name=\"users[]\" value=" . $member['user_id'] . "></td></tr>" . PHP_EOL;

                        } elseif (count(array_intersect($secondBnIds, $memberIDs)) != 0) {

                            $secondBnMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td><td><input type=\"checkbox\" name=\"users[]\" value=" . $member['user_id'] . "></td></tr>" . PHP_EOL;

                        } elseif (count(array_intersect($ssIds, $memberIDs)) != 0) {

                            $ssMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td><td><input type=\"checkbox\" name=\"users[]\" value=" . $member['user_id'] . "></td></tr>" . PHP_EOL;

                        } else {

                            $unsortedMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td><td><input type=\"checkbox\" name=\"users[]\" value=" . $member['user_id'] . "></td></tr>" . PHP_EOL;

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

                        //Get Milpacs Position
                        $position = $model->milpacsPosition($memberID);

                        if (count(array_intersect($firstBnIds, $memberIDs)) != 0) {

                            $firstBnMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td></tr>" . PHP_EOL;

                        } elseif (count(array_intersect($secondBnIds, $memberIDs)) != 0) {

                            $secondBnMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td></tr>" . PHP_EOL;

                        } elseif (count(array_intersect($ssIds, $memberIDs)) != 0) {

                            $ssMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td></tr>" . PHP_EOL;

                        } else {

                            $unsortedMemberList .= "<tr><td><a href=" . $userUrl . $member['user_id'] . "><b>" . $member['username'] . "</b></a></td><td>" . $position . "</td><td>" . date('dMy', $memberLastPost) . "</td><td>" . date('dMy', $memberLastPost + $awolTime) . "</td><td>" . $daysAwol . " day(s)</td></tr>" . PHP_EOL;

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
    
    public function actionPost() 
    {

        //Action can only be called via post
        $this->_assertPostOnly();

        // Get Current user
        $visitor = XenForo_Visitor::getInstance()->toArray();
        
        // get user values
        $users = $_POST['users'];
        $awolOption = $this->_input->filterSingle('awol_option', XenForo_Input::STRING);
        $battalion = $this->_input->filterSingle('battalion', XenForo_Input::STRING);
        $postCreation = XenForo_Application::get('options')->enableAWOLPostCreation;

        switch($awolOption)
        {
            case '1':
                // Create AWOL reminder PM
                $messageText		= XenForo_Application::get('options')->awolPMText;
                $subject            = "AWOL! ***IMPORTANT*** " . date('dM');
                $redirect = XenForo_Link::buildPublicLink('awoltracker');

                array_push($users, $visitor['user_id']);

                $message = $messageText;
                $this->createConversation($users, $subject, $message);
                
                $phrase = new XenForo_Phrase('PM Created');
                break;
            case '2':
                // Create AWOL reminder Post
                if ($postCreation) {
                    $threadID = $this->createThread(XenForo_Application::get('options')->awolRemindForum, $this->awolReminderContent($users, $battalion, $visitor));
                }
                $phrase = new XenForo_Phrase('Thread Created');
                $redirect = XenForo_Link::buildPublicLink('threads', array('thread_id' => $threadID));
                break;
            case '3':
                // Create AWOL DISCH post
                if ($postCreation) {
                    $threadID = $this->createThread(XenForo_Application::get('options')->awolDischForum, $this->awolDischContent($users, $battalion, $visitor));
                }
                $phrase = new XenForo_Phrase('Thread Created');
                $redirect = XenForo_Link::buildPublicLink('threads', array('thread_id' => $threadID));
                break;
            
        }

        // redirect back to the normal scratchpad index page
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            $redirect,
            $phrase
        );

    }

    public function createConversation($recipients, $subject, $message,
                                              $noInvites = true, $conversationClosed = false, $markReadForSender = true)
    {
        $sender = $this->getBot();
        /** @var $conversationDw XenForo_DataWriter_ConversationMaster */
        $conversationDw = XenForo_DataWriter::create('XenForo_DataWriter_ConversationMaster');
        $conversationDw->set('user_id', $sender['userID']);
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
                $conversation['conversation_id'], $sender['userID'], XenForo_Application::$time
            );
        }

        return $conversationDw->getMergedData();
    }
    
    public function awolReminderContent($users, $battalion, $visitor)
    {
        $title = "";
        $message = "";
        $newLine = "\n";

        // Create a new DateTime object
        $nextSunday = date("jS M Y", strtotime('next sunday'));

        $intro = "[i]The following troopers are hereby declared AWOL. 
        They have until Sunday " . $nextSunday ." @ 2300 Zulu, to post on the forums, reply to this thread, 
        or have someone from their CoC post on their behalf. Failure to do so will result in discharge 
        from the 7th Cavalry Regiment:[/i]";

        $daysTillInt 		= XenForo_Application::get('options')->awolDaysTill;
        $awolTime = ($daysTillInt * 86400);
        $model = $this->_getAwolModel();
        $home = XenForo_Application::get('options')->homeURL;

        $table = "[table]" . $newLine . "|-". $newLine  . "| class=\"primaryContent\" colspan=\"4\" align=\"center\" | AWOL Tracking" .
            $newLine . "|- " . $newLine . "| style=\"font-style: italic\" align=\"center\" |Member" . $newLine . "| style=\"font-style: italic\" align=\"center\" |Position" .
            $newLine . "| style=\"font-style: italic\" align=\"center\" |Last Post" . $newLine . "| style=\"font-style: italic\" align=\"center\" |Day(s) AWOL" . $newLine . "|-" .
            $newLine;

        // Generate table
        foreach($users as $user) {

            //Get AWOL stats
            $memberLastPost = $model->memberLastPost($user);
            $lastPostDate = date('dMy', $memberLastPost);
            $sinceLastPost = (time() - $memberLastPost);
            $daysAwol = round(($sinceLastPost - $awolTime) / 86400);

            // Get milpacs positon
            $position = $model->milpacsPosition($user);

            // Get username
            $username = $model->getUsername($user);

            // Build username
            $username = '[B][URL="http://' .$home.'/members/'.$user.'/"]'. $username.'[/URL][/B]';

            $table .= "| align=\"center\" |". $username . " || align=\"center\" |" . $position . " || align=\"center\" |" . $lastPostDate. "|| align=\"center\" |" . $daysAwol . $newLine . "|-" . $newLine;
        }
        // Close table
        $table .= "[/table]";

        $submittedURL = '[URL="http://' .$home.'/members/'.$visitor['user_id'].'/"]'.$visitor['username'].'[/URL]';
        $submittedBy = '[Size=3][I]Submitted by - ' . $submittedURL . '[/I][/Size]';

        switch ($battalion)
        {
            case '1': $title = "1st Battalion AWOL's | " . date('d M Y'); break;
            case '2': $title = "2nd Battalion AWOL's | " . date('d M Y'); break;
            case '3': $title = "Regimental AWOL's | " . date('d M Y'); break;
            default: $title = "Regimental AWOL's | " . date('d M Y'); break;
        }

        $message = $intro . $newLine . $newLine . $table . $newLine . $newLine . $submittedBy;

        return $content = array(
            'title' => $title,
            'message' => $message
        );
    }
    
    public function awolDischContent($users, $battalion, $visitor)
    {
        $title = "";
        $message = "";
        $newLine = "\n";
        $model = $this->_getAwolModel();
        $home = XenForo_Application::get('options')->homeURL;

        switch ($battalion)
        {
            case '1': $title = "1st Battalion AWOL Discharges | " . date('d M Y'); break;
            case '2': $title = "2nd Battalion AWOL Discharges | " . date('d M Y'); break;
            case '3': $title = "Regimental AWOL Discharges | " . date('d M Y'); break;
            default: $title = "Regimental AWOL Discharges | " . date('d M Y'); break;
        }

        $intro = "[i]The following troopers are hereby discharged for being AWOL and are eligible to re-enlist with command staff approval:[/i]";



        $table = "[table]" . $newLine . "|-". $newLine  . "| class=\"primaryContent\" colspan=\"4\" align=\"center\" | AWOL Tracking" .
            $newLine . "|- " . $newLine . "| style=\"font-style: italic\" align=\"center\" |Member" . $newLine . "| style=\"font-style: italic\" align=\"center\" |Position" .
            $newLine . "|-" . $newLine;

        // Generate table
        foreach($users as $user) {

            // Get milpacs positon
            $position = $model->milpacsPosition($user);

            // Get username
            $username = $model->getUsername($user);

            // Build username
            $username = '[B][URL="http://' .$home.'/members/'.$user.'/"]'. $username.'[/URL][/B]';

            $table .= "| align=\"center\" |". $username . " || align=\"center\" |" . $position . $newLine . "|-" . $newLine;
        }
        // Close table
        $table .= "[/table]";

        $submittedURL = '[URL="http://' .$home.'/members/'.$visitor['user_id'].'/"]'.$visitor['username'].'[/URL]';
        $submittedBy = '[Size=3][I]Submitted by - ' . $submittedURL . '[/I][/Size]';

        $message = $intro . $newLine . $newLine . $table . $newLine . $newLine . $submittedBy;

        return $content = array(
            'title' => $title,
            'message' => $message
        );
    }

    public function createThread($forumID, $content)
    {
        $poster = $this ->getBot();
        // write the thread
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $writer->set('user_id', $poster['userID']);
        $writer->set('username', $poster['username']);
        $writer->set('title', $content['title']);
        $postWriter = $writer->getFirstMessageDw();
        $postWriter->set('message', $content['message']);
        $writer->set('node_id', $forumID);
        $writer->preSave();
        $writer->save();
        return $writer->getDiscussionId();
    }

    protected function _getAwolModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_Awol' );
    }
}
