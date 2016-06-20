<?php

class CavTools_ControllerPublic_EnlistmentForm extends XenForo_ControllerPublic_Abstract {
    
    /**
     * GET RRD
     * BOT VALUES
     * 
     * @return array of userID => INT, username => STRING
     */
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

    /**
     * GET RRD
     * BOT VALUES
     * 
     * @throws 
     * @return array of View, route, array $viewParams
     */

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
        $viewParams = array();

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_EnlistmentForm', 'CavTools_Enlistmentform', $viewParams);
    }

    /**
     * AFTER FORM
     * POST RESPONSE
     *
     * @return array of userID => INT, username => STRING
     */

    public function actionPost()
    {
        // get the user_id from the user
        $visitor  = XenForo_Visitor::getInstance()->toArray();
        $userID   = $visitor['user_id'];

        // get form values
        $lastName  = $this->_input->filterSingle('last_name', XenForo_Input::STRING);
        $firstName = $this->_input->filterSingle('first_name', XenForo_Input::STRING);
        $recruiter  = $this->_input->filterSingle('recruiter', XenForo_Input::STRING);
        $age       = $this->_input->filterSingle('age', XenForo_Input::STRING);
        $timezone  = $this->_input->filterSingle('timezone', XenForo_Input::STRING);
        $date      = time();
        $steamID   = $this->_input->filterSingle('steamID', XenForo_Input::STRING);
        $inClan     = $this->_input->filterSingle('in_clan', XenForo_Input::STRING);
        $pastClans = $this->_input->filterSingle('past_clans', XenForo_Input::STRING);
        $game      = $this->_input->filterSingle('game', XenForo_Input::STRING);
        $reenlisting  = $this->_input->filterSingle('reenlistment', XenForo_Input::STRING);
        $militaryExp  = $this->_input->filterSingle('miltary_exp', XenForo_Input::STRING);
        $branchDur  = $this->_input->filterSingle('branch_dur', XenForo_Input::STRING);
        $militaryMOS  = $this->_input->filterSingle('military_mos', XenForo_Input::STRING);

        // capitalise the first letters of the first and last name, even if uppercase already
        $firstName = ucwords($firstName);
        $lastName = ucwords($lastName);
        $cavName = '';
        $cavName = $lastName . "." . $firstName[0];
        $vacValue = $this->checkVac($steamID);
        $ageValue = $this->checkAge($age);
        
        if($vacValue == 1) {
            $vacStatus = true;
        } else {
            $vacStatus = false;
        }
        
        if($ageValue == 1) {
            $ageStatus = true;
        } else {
            $ageStatus = false;
        }

        if ($reenlisting == "Yes") {
            $reenlistment = true;
        } else {
            $reenlistment = false;
        }

        if ($inClan == "Yes") {
            $clanStatus = true;
        } else {
            $clanStatus = false;
        }

        // check if they have military past
        if ($militaryExp == "Yes") {
            $military = true;
        } else {
            $military = false;
        }

        // create enlistment thread
        $thread = $this->createThreadContent($lastName, $firstName, $reenlistment, $timezone,
            $game, $inClan, $pastClans, $steamID, $age, $military, $branchDur, $militaryMOS, $cavName, $visitor);

        //Get values from options
        $forumID  = XenForo_Application::get('options')->enlistmentForumID;

        $threadID = $this->actionCreateThread($forumID, $thread['title'], $thread['message']);
        $post     = $this->actionCreatePost($threadID, $this->createPostContent($steamID, $cavName, $age, $reenlistment));
        
        if ($ageValue != 1 && $vacValue != 1) {
            if ($reenlistment == true) {
                // Get values from options
                $rrdOIC = XenForo_Application::get('options')->rrdOICuserID;
                $rrdXO = XenForo_Application::get('options')->rrdXOuserID;
                $rrdNCOIC = XenForo_Application::get('options')->rrdNCOICuserID;


                $recipients = array($rrdOIC, $rrdXO, $rrdNCOIC, $userID);
                $pm = $this->createPMContent($cavName, $date, $userID, $steamID);

                $this->createConversation($recipients, $pm,
                    $noInvites = false, $conversationClosed = false, $markReadForSender = true);
            }
        }

        // write data into db
        $this->actionWrite($userID, $recruiter, $lastName, $firstName, $age,
            $timezone, $date, $steamID, $clanStatus, $pastClans,
            $game, $reenlistment, $threadID, $vacStatus, $ageStatus);

        // redirect after post
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('threads', array('thread_id' => $threadID)),
            new XenForo_Phrase('application_received')
        );
    }
    
    /*
     * CREATE THE
     * ENLISTMENT THREAD
     */

    public function createThreadContent($lastName, $firstName, $reenlistment, $timezone,
                                        $game, $inClan, $pastClans, $steamID, $age, $military,
                                        $branchDur, $militaryMOS, $cavName, $visitor)
    {
        $status = "";
        $newLine = "\n";
        $denied = false;
        $home = XenForo_Application::get('options')->homeURL;

        $checks = "";

        $checkVac = $this->checkVac($steamID);
        $checkName = $this->checkName($cavName);
        $checkAge = $this->checkAge($age);
        
        if($reenlistment = false)
        {
            $reenlisting = "yes";
            $heading = '[Re-Enlistment] - ';
        } else {
            $reenlisting = "no";
            $heading = '[Enlistment] - ';
        }

        if($military) {
            $militaryText = $newLine . '[B]Military Experience[/B]' . $newLine . 'Yes' . $newLine . '[B]Branch & duration[/B]' .
                $newLine . $branchDur . $newLine . '[B]Military Occupational Specialty[/B]' . $newLine. $militaryMOS;
        } else {
            $militaryText = $newLine . 'None';
        }
        $submittedURL = '[URL="http://' .$home.'/members/'.$visitor['user_id'].'"]'.$visitor['username'].'[/URL]';
        $submittedBy = '[I]Submitted by - ' . $submittedURL;

        if (!$this->checkDenied($checkVac, $checkAge)) {
            $title = $heading . $cavName . ' - ' . $game;
        } else {
            $title = $heading .$cavName. ' - ' . $game.' - DENIED';
        }
        $message = '[Size=6][B]Name choice[/B][/Size]' . $newLine . '[B]Last Name[/B]' . $newLine . $lastName . $newLine .
            '[B]First Name[/B]' . $newLine . $firstName . $newLine . $newLine . '[Size=6][B]Personal Information[/B][/Size]' .
            $newLine . '[B]Re-enlistment[/B]' . $newLine . $reenlisting . $newLine . '[B]Time zone[/B]' . $newLine .
            $timezone . $newLine . $newLine . '[Size=6][B]Game Information[/B][/Size]' . $newLine. '[B]Game applied for[/B]' .
            $newLine . $game . $newLine . '[B]Clan status[/B]' . $newLine . $inClan . $newLine . '[B]Past Clans[/B]'. $newLine. $pastClans .
            $newLine . $newLine . '[Size=6][B]Military Information[/B][/Size]' . $militaryText . $newLine . $newLine . $submittedBy;

        return array('title' => $title, 'message' => $message);

    }
    
    public function actionCreateThread($forumID, $title, $message)
    {
        // get rrd bot values
        $poster = $this->getRRDBot();

        // write the thread
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $writer->set('user_id', $poster['userID']);
        $writer->set('username', $poster['username']);
        $writer->set('title', $title);
        $postWriter = $writer->getFirstMessageDw();
        $postWriter->set('message', $message);
        $writer->set('node_id', $forumID);
        $writer->preSave();
        $writer->save();
        return $writer->getDiscussionId();
    }
    
    /*
     * CREATE THE
     * ENLISTMENT REPLY
     */

    public function createPostContent($steamID, $cavName, $age, $reenlistment)
    {
        $checkVac = $this->checkVac($steamID);
        $checkName = $this->checkName($cavName);
        $checkAge = $this->checkAge($age);

        $newLine = "\n";

        $banText = $this->banText($checkVac);
        $nameText = $this->nameText($checkName);
        $requireText = $this->requirementText($checkAge);
        $checks = $banText . $newLine . $newLine .$nameText . $newLine . $newLine . $requireText;

        $messageReply = $checks;
        if ($this->checkDenied($checkVac, $checkAge)) {
            if ($checkVac == 1) {
                $messageReply .= $newLine . $newLine. 'Vac ban on record. Application denied.';
            }
            if ($checkAge == 1) {
                $messageReply .= $newLine . $newLine . 'Applicant under age. Application denied.';
            }
        } else if($reenlistment) {
            $messageReply .= $newLine . $newLine . 'Check your inbox for more information.';
        } else {
            $messageReply .= $newLine . $newLine . 'A recruiter will contact you shortly.';
        }

        return $messageReply;
    }

    public function actionCreatePost($threadId, $message)
    {
        // write the first reply post
        $threadModel = XenForo_Model::create('XenForo_Model_Thread');
        $poster = $this->getRRDBot();
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
        $writer->set('user_id', $poster['userID']);
        $writer->set('username', $poster['username']);
        $writer->set('message', $message);
        $writer->set('message_state', 'visible');
        $writer->set('thread_id', $threadId);
        $writer->save();
    }

    public function createPMContent($cavName, $date, $userID, $steamID)
    {
        $title = '[Re-enlistment] - ' . $cavName . ' - ' . $date;

        $relationID = $this->_getEnlistmentModel()->getRelationID($userID);
        $home = XenForo_Application::get('options')->homeURL;
        $pmText = XenForo_Application::get('options')->reenlistmentText;
        $milpacsLink = "[URL=http://". $home ."/rosters/profile?uniqueid=" . $relationID['relation_id']."]Milpacs Profile[/URL]";
        $lastRecord = $this->_getEnlistmentModel()->getLastRecord($relationID['relation_id']);
        $newLine = "\n";

        $checkVac = $this->checkVac($steamID);
        $checkName = $this->checkName($cavName);
        $banText = $this->banText($checkVac);
        $nameText = $this->nameText($checkName);
        $checks = $banText . $newLine . $nameText;


            $message = '[Size=7][B]Welcome back[/B][/Size]'. $newLine . $newLine . $pmText . $newLine . $newLine .  '[Size=6][B]STAFF NOTE[/B][/Size]'.
                $newLine . $milpacsLink . $newLine . date("m.d.y",$lastRecord['record_date']) . ' - ' .$lastRecord['details'] . $newLine . $newLine .$checks;

        return $pm = array('title' => $title, 'message' => $message);
    }

    public function createConversation(array $recipients, array $pm,
                                       $noInvites = false, $conversationClosed = false, $markReadForSender = true)
    {

        $rrdBot = $this->getRRDBot();

        $conversationDw = XenForo_DataWriter::create('XenForo_DataWriter_ConversationMaster');
        $conversationDw->set('user_id', $rrdBot['userID']);
        $conversationDw->set('username', $rrdBot['username']);
        $conversationDw->set('title', $pm['title']);
        if ($noInvites) {
            $conversationDw->set('open_invite', 0);
        }
        if ($conversationClosed) {
            $conversationDw->set('conversation_open', 0);
        }

        $conversationDw->addRecipientUserIds($recipients);
        $messageDw = $conversationDw->getFirstMessageDw();
        $messageDw->set('message', $pm['message']);
        $conversationDw->preSave();
        $conversationDw->save();
        $conversation = $conversationDw->getMergedData();

        $convModel = XenForo_Model::create('XenForo_Model_Conversation');
        if ($markReadForSender) {
            $convModel->markConversationAsRead(
                $conversation['conversation_id'], $rrdBot['userID'], XenForo_Application::$time
            );
        }

        return $conversationDw->getMergedData();
    }


    
    /*
     * WRITE TO
     * ENLISTMENT TABLE
     */

    public static function actionWrite($userID, $recruiter, $lastName, $firstName, $age,
                                    $timezone, $date, $steamID, $inClan, $pastClans,
                                    $game, $reenlistment, $threadID, $vacStatus, $ageStatus)
    {
        // write the enlistee details to the db
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
        $dwEnlistment->set('past_clans', $pastClans);
        $dwEnlistment->set('game', $game);
        $dwEnlistment->set('reenlistment', $reenlistment);
        $dwEnlistment->set('thread_id', $threadID);
        $dwEnlistment->set('vac_ban', $vacStatus);
        $dwEnlistment->set('under_age', $ageStatus);
        $dwEnlistment->save();
    }
    
    /*
     * CHECK FORM
     * VALUES OUTPUT
     */

    public function checkVac($steamID)
    {
        // curl http://api.steampowered.com/ISteamUser/GetPlayerBans/v1/?key=$key&steamids=$ID

        //Set variables
        $key = XenForo_Application::get('options')->steamAPIKey;
        $ID = $steamID;
        $url   = sprintf("http://api.steampowered.com/ISteamUser/GetPlayerBans/v1/?key=%s&steamids=%s", $key, $ID);

        //Send curl message
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $reply = curl_exec($ch);
        curl_close($ch);

        $reply = json_decode($reply, true);
        $banned = 0;
        try {
            if ($reply['players'][0]['VACBanned'] == true) {
                $banned = 1;
            } else if ($reply['players'][0]['VACBanned'] == false) {
                $banned = 2;
            }
        }catch (Exception $e) {
            $banned = 3;
        }
        return $banned;
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

    public function checkAge($age)
    {
        try {
            if ($age >= 18)
            {
                return 2;
            } else if ($age < 18) {
                return 1;
            }
        } catch (Exception $e)
        {
            return 3;
        }
    }
    
    public function checkDenied($checkVac, $checkAge)
    {   
        $denied = false;
        
        if ($checkAge == 1 || $checkVac == 1)
        {
            $denied = true;
        }
        return $denied;
    }
    
    /*
     * TEXT CREATION
     * FOR CHECKS
     */

    public function banText($banned)
    {
        switch($banned)
        {
            case 1:
                return "[Size=6][COLOR=red][B]VAC BAN[/B][/COLOR][/SIZE]";
            case 2:
                return "[Size=6][COLOR=green][B]NO VAC BAN[/B][/COLOR][/SIZE]";
            case 3:
                return "[Size=6][COLOR=yellow][B]BAN CHECK FAILED[/B][/COLOR][/SIZE]";
            default:
                return "[Size=6][COLOR=yellow][B]BAN CHECK FAILED[/B][/COLOR][/SIZE]";
        }
    }

    public function nameText($count)
    {
        switch($count)
        {
            case 1:
                return "[Size=6][COLOR=red][B]NAME TAKEN[/B][/COLOR][/SIZE]";
            case 2:
                return "[Size=6][COLOR=green][B]NAME FREE[/B][/COLOR][/SIZE]";
            case 3:
                return "[Size=6][COLOR=yellow][B]NAME CHECK FAILED[/B][/COLOR][/SIZE]";
            default:
                return "[Size=6][COLOR=yellow][B]NAME CHECK FAILED[/B][/COLOR][/SIZE]";
        }
    }

    public function requirementText($count)
    {
        switch($count)
        {
            case 1:
                return "[Size=6][COLOR=red][B]REQUIREMENTS NOT MET[/B][/COLOR][/SIZE]";
            case 2:
                return "[Size=6][COLOR=green][B]REQUIREMENTS MET[/B][/COLOR][/SIZE]";
            case 3:
                return "[Size=6][COLOR=yellow][B]REQUIREMENTS CHECK FAILED[/B][/COLOR][/SIZE]";
            default:
                return "[Size=6][COLOR=yellow][B]REQUIREMENTS CHECK FAILED[/B][/COLOR][/SIZE]";
        }
    }
    
    /*
     * GET THE
     * ENLISTMENT MODEL
     */

    protected function _getEnlistmentModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_Enlistment' );
    }
}