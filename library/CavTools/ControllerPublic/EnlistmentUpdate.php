<?php

class CavTools_ControllerPublic_EnlistmentUpdate extends XenForo_ControllerPublic_Abstract {

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

    public function actionIndex() {

        //Get values from options
        $enable = XenForo_Application::get('options')->enableEnlistmentFormUpdate;

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'EnlistmentFormUpdate'))
        {
            throw $this->getNoPermissionResponseException();
        }

        //Set Time Zone to UTC
        date_default_timezone_set("UTC");

        $games = XenForo_Application::get('options')->games;

        $games = explode(',', $games);

        //View Parameters
        $viewParams = array(
            'games' => $games
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_EnlistmentUpdate', 'CavTools_EnlistmentUpdate', $viewParams);
    }

    public function actionPost()
    {
        $visitor  = XenForo_Visitor::getInstance()->toArray();

        // get form values
        $enlistmentID = $this->_input->filterSingle('enlistment_id', XenForo_Input::INT);
        $lastName  = $this->_input->filterSingle('last_name', XenForo_Input::STRING);
        $firstName = $this->_input->filterSingle('first_name', XenForo_Input::STRING);
        $recruiter  = $this->_input->filterSingle('recruiter', XenForo_Input::STRING);
        $steamID   = $this->_input->filterSingle('steamID', XenForo_Input::STRING);
        $inClan     = $this->_input->filterSingle('in_clan', XenForo_Input::STRING);
        $pastClans = $this->_input->filterSingle('past_clans', XenForo_Input::STRING);
        $reenlisting  = $this->_input->filterSingle('reenlistment', XenForo_Input::STRING);
        if(($this->_input->filterSingle('gameChange', XenForo_Input::STRING)) == "Yes") {
            $game = $this->_input->filterSingle('game', XenForo_Input::STRING);
        } else {
            $game = false;
        }
        if(($this->_input->filterSingle('timezoneChange', XenForo_Input::STRING)) == "Yes") {
            $timezone = $this->_input->filterSingle('timezone', XenForo_Input::STRING);
        } else {
            $timezone = false;
        }

        // Check if already completed
        $model = $this->_getEnlistmentModel();
        $canBeUpdated = $model->canUpdate($enlistmentID);
        $enlistmentExists = $model->checkEnlistment($enlistmentID);

        if($canBeUpdated && $enlistmentExists) {
            $this->updateEnlistment($enlistmentID, $lastName, $firstName, $recruiter, $inClan, $pastClans, $reenlisting,
                                    $game, $timezone, $steamID);
            $response = new XenForo_Phrase('Enlistment Updated');
        } else if (!$enlistmentExists) {
            $response = new XenForo_Phrase('Enlistment does not exist');
        } else {
            $response = new XenForo_Phrase('Enlistment already completed');
        }

        $this->tweet($visitor, $enlistmentID);

        // redirect after post
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('enlistments'),
            $response
        );
    }

    public function updateEnlistment($enlistmentID, $lastName, $firstName, $recruiter, $inClan, $pastClans, $reenlisting,
                                     $game, $timezone, $steamID)
    {
        // get the user_id from the user
        $visitor  = XenForo_Visitor::getInstance()->toArray();



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
        $vacValue = $this->checkVac($steamID);

        $nameUpdated = false;
        $banUpdated = false;
        $timeZoneUpdated = false;
        $inClanUpdated = false;
        $gameUpdated = false;
        $reenlistingUpdated = false;

        $enlistModel = $this->_getEnlistmentModel();
        $currentData = $enlistModel->getEnlistmentById($enlistmentID);

        // write the enlistee details to the db
        $dw = XenForo_DataWriter::create('CavTools_DataWriter_Enlistments');
        $dw->setExistingData($enlistmentID);
        if ($lastName || $firstName) {
            if ($lastName == '') {
                $lastName = $currentData['last_name'];
            }else {
                $lastName = ucwords($lastName);
                $dw->set('last_name', $lastName);
            }
            if ($firstName == '') {
                $firstName = $currentData['first_name'];
            } else {
                $firstName = ucwords($firstName);
                $dw->set('first_name', $firstName);
                $lastName = $currentData['last_name'];
            }
            $nameUpdated = true;
        }
        if ($timezone) {
            $dw->set('timezone', $timezone);
            $timeZoneUpdated = true;
        }
        if ($steamID) {
            $dw->set('steamID', $steamID);
            if ($vacValue) {
                $dw->set('vac_ban', $vacValue);
                $banUpdated = true;
                if ($vacValue == 1) {
                    $dw->set('current_status', 1);
                }
            }
        }
        if ($recruiter) {
            $dw->set('recruiter', $recruiter);
        }
        if ($inClan) {
            $dw->set('in_clan', $clanStatus);
            if ($pastClans) {
                $dw->set('past_clans', $pastClans);
            }
            $inClanUpdated = true;
        }
        if ($game) {
            $dw->set('game', $game);
            $gameUpdated = true;
        }
        if ($reenlisting) {
            $dw->set('reenlistment', $reenlistment);
            $reenlistingUpdated = true;
        }
        $dw->set('last_update', XenForo_Application::$time);


        $query = $enlistModel->getEnlistmentById($enlistmentID);
        $firstName = ucwords($firstName);
        $lastName = ucwords($lastName);
        $cavName = '';
        $cavName = $lastName . "." . $firstName[0];
        $newline = "\n";
        $home = XenForo_Application::get('options')->homeURL;
        $submittedURL = '[URL="http://' .$home.'/members/'.$visitor['user_id'].'"]'.$visitor['username'].'[/URL]';
        $submittedBy = '[Size=3][I]Submitted by - ' . $submittedURL . '[/I][/Size]';

        $postContent = '';
        $action = 'Updates: ';
        if ($nameUpdated) {
            $postContent .= "[B]Name updated[/B]" . $newline . $newline;
            $postContent .= $this->createNamePostContent($cavName, $query['user_id']) .$newline .$newline .$newline;
            $action .= "Name, ";
        }
        if ($banUpdated) {
            $postContent .= "[B]Steam ID updated[/B]" . $newline . $newline;
            $postContent .= $this->createVacPostContent($steamID) .$newline .$newline .$newline;
            $action .= "SteamID, ";
        }
        if ($reenlistingUpdated) {
            $postContent .= "[B]Re-enlistment Updated[/B]" . $newline ."Re-enlistment Status: " .$reenlisting.
                $newline . $newline . $newline;
            $action .= "enlistment type, ";
        }
        if ($timeZoneUpdated) {
            $postContent .= "[B]Time Zone Updated[/B]" . $newline ."TimeZone: " .$timezone.
                $newline . $newline . $newline;
            $action .= "Timezone, ";
        }
        if ($inClanUpdated) {
            $postContent .= "[B]Clan status Updated[/B]" . $newline ."Clan Status: " .$inClan.
                $newline . $newline .$newline;
            $action .= "Clan status, ";
        }
        if ($gameUpdated) {
            $postContent .= "[B]Game Updated[/B]" . $newline ."Game applied for: " .$game.
                $newline . $newline . $newline;
            $action .= "Game, ";
        }
        if ($recruiter) {
            $postContent .= "[B]Recruiter Updated[/B]" . $newline ."Recruiter: " .$recruiter.
                $newline . $newline . $newline;
            $action .= "Recruiter, ";
        }

        $this->writeLog($enlistmentID, $action);

        $denied = false;
        if ($vacValue == 1) {
            $denied = true;
        }

        if (!$denied) {
            if ($reenlistment == true) {
                // Get values from options
                $rrdOIC = XenForo_Application::get('options')->rrdOICuserID;
                $rrdXO = XenForo_Application::get('options')->rrdXOuserID;
                $rrdNCOIC = XenForo_Application::get('options')->rrdNCOICuserID;


                $recipients = array($rrdOIC, $rrdXO, $rrdNCOIC, $query['user_id']);
                $pm = $this->createPMContent($enlistmentID);

                $this->createConversation($recipients, $pm,
                    $noInvites = false, $conversationClosed = false, $markReadForSender = true);
            }
        }
        $dw->save();

        $this->actionCreatePost($query['thread_id'], $postContent, $submittedBy);
        $this->updateThread($query['thread_id'],$this->rebuildTitle($enlistmentID));
    }

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

    public function createPMContent($enlistmentID)
    {
        $enlistModel = $this->_getEnlistmentModel();
        $query = $enlistModel->getEnlistmentById($enlistmentID);
        $firstName = ucwords($query['first_name']);
        $lastName = ucwords($query['last_name']);
        $cavName = '';
        $cavName = $lastName . "." . $firstName[0];

        $title = '[Re-enlistment] - ' . $cavName . ' - ' . date("m.d.y",$query['enlistment_date']);

        $relationID = $this->_getEnlistmentModel()->getRelationID($query['user_id']);
        $home = XenForo_Application::get('options')->homeURL;
        $pmText = XenForo_Application::get('options')->reenlistmentText;
        $milpacsLink = "[URL=http://". $home ."/rosters/profile?uniqueid=" . $relationID['relation_id']."]Milpacs Profile[/URL]";
        $lastRecord = $this->_getEnlistmentModel()->getLastRecord($relationID['relation_id']);
        $newLine = "\n";

        $checkVac = $this->checkVac($query['steamID']);
        $checkName = $this->checkName($cavName, $query['user_id']);
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

    public function createVacPostContent($steamID)
    {
        $checkVac = $this->checkVac($steamID);

        $newLine = "\n";

        $banText = $this->banText($checkVac);
        $checks = $banText;

        $messageReply = $checks;
        if ($checkVac == 1) {
            $messageReply .= $newLine . $newLine. '[B]Vac ban on record. Application denied.[/B]';
        }

        return $messageReply;
    }

    public function createNamePostContent($cavName, $userID)
    {
        $checkName = $this->checkName($cavName, $userID);

        $newLine = "\n";

        $nameText = $this->nameText($checkName);
        $checks = $nameText;

        $messageReply = $checks;

        return $messageReply;
    }

    public function actionCreatePost($threadId, $postContent, $submittedBy)
    {
        $newline = "\n";
        $message = $postContent . $newline .$newline .$submittedBy;
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

    public function rebuildTitle($enlistmentID)
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

    public function updateThread($threadID, $title)
    {
        // update the thread
        //Get values from options
        $dw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $dw->setExistingData($threadID);
        $dw->set('title', $title);
        $dw->save();
    }

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

    protected function _getEnlistmentModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_Enlistment' );
    }

    public function tweet($visitor, $enlistmentID)
    {
        $text = $visitor['username'] . " just used the recruitment updater, helping out enlistee #" . $enlistmentID . " join the Cav!";
        $hashtag = "#RRD #7Cav #IMO";

        $twitterModel = $this->_getTwitterBot();
        $twitterModel->postStatus($text, $hashtag);
    }

    protected function _getTwitterBot()
    {
        return $this->getModelFromCache( 'CavTools_Model_IMOBot' );
    }

}
