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

    public function enlistmentCheck($userID)
    {
        $enlistmentModel = $this->_getEnlistmentModel();
        $enlistments     = $enlistmentModel->getEnlistmentsByUser($userID);
        $status          = false;
        if ($enlistments) {
            foreach ($enlistments as $enlistment) {
                if ($enlistment['hidden'] == 0) {
                    $status = true;
                }
            }
        }
        return $status;
    }

    public function getSteamProfile($ID)
    {
        //Set variables
        $key = XenForo_Application::get('options')->steamAPIKey;
        $url   = sprintf("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=%s&steamids=%s", $key, $ID);

        //Send curl message
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $reply = curl_exec($ch);
        curl_close($ch);

        $reply = json_decode($reply, true);

        try {
            $name    = $reply['response']['players'][0]['personaname'];
            $profile = $reply['response']['players'][0]['personastate'];
            $avatar  = $reply['response']['players'][0]['avatarfull'];
            $url     = $reply['response']['players'][0]['profileurl'];
        } catch (Exception $e) {
            $name    = "Invalid SteamID given";
            $profile = 7;
            $avatar  = "http://placehold.it/184x184";
            $url     = '#';
        }

        try {
            $visability = $reply['response']['players'][0]['communityvisibilitystate'];
            if ($visability == 1 || $visability == 2) {
                $status = 1;
            } else if ($visability == 3) {
                $status = 2;
            }
        }catch (Exception $e) {
            $status = 3;
        }

        return array(
            'avatar' => $avatar,
            'personaname' => $name,
            'personastate' => $profile,
            'status' => $status,
            'url' => $url
        );
    }

    /**
     * LOAD INDEX
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

        //Get values from options
        $steamID_thread = XenForo_Application::get('options')->steamIDthread;
        $steamID_thread = "threads/".$steamID_thread;
        $minRequire_thread = XenForo_Application::get('options')->minRequireThread;
        $minRequire_thread = "threads/".$minRequire_thread;
        $games = XenForo_Application::get('options')->games;

        $games = explode(',', $games);

        $visitor  = XenForo_Visitor::getInstance()->toArray();
        $model = $this->_getEnlistmentModel();
        $hasMilpac = $model->checkMilpac($visitor['user_id']);

        $status = $model->getOpenEnlistmentsByUser($visitor['user_id']);
        $template = '';

        if ($status) {
            $template = 'CavTools_CurrentEnlistment';
            $enlistments = $model->getEnlistmentsByUser($visitor['user_id']);
            $data = array();
            foreach ($enlistments as $enlistment) {
                $row = array();

                $firstName = ucwords($enlistment['first_name']);
                $lastName  = ucwords($enlistment['last_name']);
                $cavName   = '';
                $cavName   = $lastName . "." . $firstName[0];

                $steam = $this->getSteamProfile($enlistment['steamID']);


                $enlistment['current_status'];

                switch ($enlistment['current_status'])
                {
                    case 1: $status = '<div id="red">Denied</div>';break;
                    case 2: $status = '<div id="green">Approved</div>';break;
                    case 3: $status = '<div id="yellow">Open</div>';break;
                }

                switch ($steam['status'])
                {
                    case 1: $steamStatus = '<div id="red">Private</div>';break;
                    case 2: $steamStatus = '<div id="green">Public</div>';break;
                    case 3: $steamStatus = '<div id="yellow">Invalid SteamID given</div>';break;
                }

                switch ($steam['personastate'])
                {
                    case 0: $steamState = '<div id="red">Offline</div>';break;
                    case 1:
                    case 4:
                    case 5:
                    case 6:
                            $steamState = '<div id="green">Online</div>';break;
                    case 2: $steamState = '<div id="red">Away</div>';break;
                    case 7: $steamState = '<div id="yellow">Invalid SteamID given</div>';break;
                }

                $row['status'] = $status;
                $row['cav_name'] = $cavName;
                $row['date'] = date("dMy G:i:s T", intval($enlistment['enlistment_date']));
                $row['date'] = strtoupper($row['date']);
                $row['enlistment_id'] = $enlistment['enlistment_id'];
                $row['thread_id'] = $enlistment['thread_id'];
                $row['game'] = $enlistment['game'];

                $row['steam_image'] = $steam['avatar'];
                $row['steam_username'] = $steam['personaname'];
                $row['steam_state'] = $steamState;
                $row['steam_status'] = $steamStatus;
                $row['steam_url'] = $steam['url'];
                array_push($data, $row);
            }

            $viewParams = array(
                'username' => $visitor['username'],
                'enlistments' => $data
            );
        } else {
            $template = 'CavTools_Enlistmentform';

            //View Parameters
            $viewParams = array(
                'username' => $visitor['username'],
                'hasMilpac' => $hasMilpac,
                'steamID_thread' => $steamID_thread,
                'minRequire_thread' => $minRequire_thread,
                'games' => $games
            );
        }

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_EnlistmentForm', $template, $viewParams);
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
        $lastName     = $this->_input->filterSingle('last_name', XenForo_Input::STRING);
        $firstName    = $this->_input->filterSingle('first_name', XenForo_Input::STRING);
        $recruiter    = $this->_input->filterSingle('recruiter', XenForo_Input::STRING);
        $recruiter    = substr($recruiter,0,50);
        $age          = $this->_input->filterSingle('age', XenForo_Input::STRING);
        $timezone     = $this->_input->filterSingle('timezone', XenForo_Input::STRING);
        $date         = XenForo_Application::$time;
        $steamID      = $this->_input->filterSingle('steamID', XenForo_Input::STRING);
        $origin       = $this->_input->filterSingle('origin', XenForo_Input::STRING);
        $inClan       = $this->_input->filterSingle('in_clan', XenForo_Input::STRING);
        $pastClans    = $this->_input->filterSingle('past_clans', XenForo_Input::STRING);
        $game         = $this->_input->filterSingle('game', XenForo_Input::STRING);
        $reenlisting  = $this->_input->filterSingle('reenlistment', XenForo_Input::STRING);
        $militaryExp  = $this->_input->filterSingle('miltary_exp', XenForo_Input::STRING);
        $branchDur    = $this->_input->filterSingle('branch_dur', XenForo_Input::STRING);
        $militaryMOS  = $this->_input->filterSingle('military_mos', XenForo_Input::STRING);

        // capitalise the first letters of the first and last name, even if uppercase already
        $firstName = ucwords($firstName);
        $lastName  = ucwords($lastName);
        $cavName   = '';
        $cavName   = $lastName . "." . $firstName[0];
        $vacValue  = $this->checkVac($steamID);
        $ageValue  = $this->checkAge($age);
        $denied    = false;
        $currentStatus = 3;

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

        if ($ageValue == 1 || $vacValue == 1) {
            $denied = true;
            $currentStatus = 1;
        }

        // create enlistment thread
        $thread = $this->createThreadContent($lastName, $firstName, $reenlistment, $timezone,
            $game, $inClan, $pastClans, $steamID, $age, $military, $branchDur, $militaryMOS, $cavName, $visitor);

        //Get values from options
        $forumID  = XenForo_Application::get('options')->enlistmentForumID;

        $threadID = $this->actionCreateThread($forumID, $thread['title'], $thread['message']);
        $post     = $this->actionCreatePost($threadID, $this->createPostContent($steamID, $cavName, $age, $reenlistment, $visitor));

        if (!$denied) {
            if ($reenlistment == true) {
                // Get values from options
                $rrdOIC = XenForo_Application::get('options')->rrdOICuserID;
                $rrdXO = XenForo_Application::get('options')->rrdXOuserID;
                $rrdNCOIC = XenForo_Application::get('options')->rrdNCOICuserID;


                $recipients = array($rrdOIC, $rrdXO, $rrdNCOIC, $userID);
                $pm = $this->createPMContent($cavName, $date, $userID, $steamID, $visitor);

                $this->createConversation($recipients, $pm,
                    $noInvites = false, $conversationClosed = false, $markReadForSender = true);
            }
        }

        // write data into db
        $this->actionWrite($userID, $recruiter, $lastName, $firstName, $age,
            $timezone, $date, $steamID, $clanStatus, $pastClans,
            $game, $reenlistment, $threadID, $vacValue, $ageValue, $currentStatus, $origin);

        $twitterModel = $this->_getTwitterBot();

        if ($denied)
        {
            $text    = "Sorry " . $visitor['username'] . ", your enlistment is denied!";
            $hashtag = "#Denied #7Cav #IMO";
        } else {
            $text    = "Hey " . $visitor['username'] . ", your enlistment is under review!";
            $hashtag = "#maybe? #7Cav #IMO";
        }

        $twitterModel->postStatus($text, $hashtag);

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
        $status  = "";
        $newLine = "\n";
        $denied  = false;
        $home    = XenForo_Application::get('options')->homeURL;

        $checks  = "";

        $checkVac  = $this->checkVac($steamID);
        $checkName = $this->checkName($cavName, $visitor['user_id']);
        $checkAge  = $this->checkAge($age);

        if($reenlistment == true)
        {
            $reenlisting = "yes";
            $heading     = '[Re-Enlistment] - ';
        } else {
            $reenlisting = "no";
            $heading     = '[Enlistment] - ';
        }

        if($military) {
            $militaryText = $newLine . '[B]Military Experience[/B]' . $newLine . 'Yes' . $newLine . '[B]Branch & duration[/B]' .
                $newLine . $branchDur . $newLine . '[B]Military Occupational Specialty[/B]' . $newLine. $militaryMOS;
        } else {
            $militaryText = $newLine . 'None';
        }


        $submittedURL = '[URL="http://' .$home.'/members/'.$visitor['user_id'].'"]'.$visitor['username'].'[/URL]';
        $submittedBy  = '[Size=3][I]Submitted by - ' . $submittedURL . '[/I][/Size]';

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

    public function createPostContent($steamID, $cavName, $age, $reenlistment, $visitor)
    {
        $checkVac = $this->checkVac($steamID);
        $checkName = $this->checkName($cavName, $visitor['user_id']);
        $checkAge = $this->checkAge($age);

        $newLine = "\n";

        $banText = $this->banText($checkVac);
        $nameText = $this->nameText($checkName);
        $requireText = $this->requirementText($checkAge);
        $checks = $nameText . $newLine . $newLine .$banText . $newLine . $newLine . $requireText;

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

    public function createPMContent($cavName, $date, $userID, $steamID, $visitor)
    {
        $title = '[Re-enlistment] - ' . $cavName . ' - ' . date("m.d.y",$date);

        $relationID = $this->_getEnlistmentModel()->getRelationID($userID);
        $home = XenForo_Application::get('options')->homeURL;
        $pmText = XenForo_Application::get('options')->reenlistmentText;
        $milpacsLink = "[URL=http://". $home ."/rosters/profile?uniqueid=" . $relationID['relation_id']."]Milpacs Profile[/URL]";
        $lastRecord = $this->_getEnlistmentModel()->getLastRecord($relationID['relation_id']);
        $newLine = "\n";

        $checkVac = $this->checkVac($steamID);
        $checkName = $this->checkName($cavName, $visitor['user_id']);
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
    }



    /*
     * WRITE TO
     * ENLISTMENT TABLE
     */

    public static function actionWrite($userID, $recruiter, $lastName, $firstName, $age,
                                    $timezone, $date, $steamID, $inClan, $pastClans,
                                    $game, $reenlistment, $threadID, $vacValue, $ageValue, $currentStatus, $origin)
    {

        if ($ageValue == 1) {
            $ageStatus = true;
        } else {
            $ageStatus = false;
        }

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
        $dwEnlistment->set('vac_ban', $vacValue);
        $dwEnlistment->set('under_age', $ageStatus);
        $dwEnlistment->set('current_status', $currentStatus);
        $dwEnlistment->set('last_update', $date);
        $dwEnlistment->set('origin', $origin);
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

    public function checkName($cavName, $userID)
    {
        $enlistModel = $this->_getEnlistmentModel();
        $check = $enlistModel->checkEnlistedIsOwner($userID, $cavName);

        if (!$check) {
            $query = $enlistModel->checkNameDupe($cavName);

            if ($query == null) {
                $count = 2;
            } else if ($cavName === $query[0]["username"] || $cavName === $query) {
                $count = 1;
            } else {
                $count = 3;
            }
        } else {
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

    protected function _getTwitterBot()
    {
        return $this->getModelFromCache( 'CavTools_Model_IMOBot' );
    }
}
