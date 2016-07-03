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

        if (XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'canMajorActionEnlistment'))
        {
            $canMajorAction = true;
        } else {
            $canMajorAction = false;
        }

        if (XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'canActionEnlistment'))
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
        
        $quarter = $this->getDatesOfQuarter();
        $prevQuarter = $this->getDatesOfQuarter('previous');
        $games = XenForo_Application::get('options')->games;

        $games = explode(',', $games);
        $totalGameData = array();
        foreach ($games as $game) {
            
            $current = $enlistModel->getEnlistmentsForQuarter($quarter['start'], $quarter['end'], $game);
            $previous = $enlistModel->getEnlistmentsForQuarter($prevQuarter['start'], $prevQuarter['end'], $game);
            
            $gameData = array(
                'title' => $game,
                'prev_enlist' => $previous,
                'curr_enlist' => $current
            );
            array_push($totalGameData, $gameData);
        }

        //View Parameters
        $viewParams = array(
            'totalGameData' => $totalGameData,
            'normalEnlistments' => $normalEnlistments,
            'reEnlistments' => $reEnlistments,
            'canMajorAction' => $canMajorAction,
            'canAction' => $canAction,
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_EnlistmentManagement', 'CavTools_EnlistmentManagement', $viewParams);
    }
    
    public static function getDatesOfQuarter($quarter = 'current', $year = null, $format = 'U')
    {
        if ( !is_int($year) ) {
            $year = (new DateTime)->format('Y');
        }
        $current_quarter = ceil((new DateTime)->format('n') / 3);
        switch (  strtolower($quarter) ) {
            case 'this':
            case 'current':
                $quarter = ceil((new DateTime)->format('n') / 3);
                break;

            case 'previous':
                $year = (new DateTime)->format('Y');
                if ($current_quarter == 1) {
                    $quarter = 4;
                    $year--;
                } else {
                    $quarter =  $current_quarter - 1;
                }
                break;

            case 'first':
                $quarter = 1;
                break;

            case 'last':
                $quarter = 4;
                break;

            default:
                $quarter = (!is_int($quarter) || $quarter < 1 || $quarter > 4) ? $current_quarter : $quarter;
                break;
        }
        if ( $quarter === 'this' ) {
            $quarter = ceil((new DateTime)->format('n') / 3);
        }
        $start = new DateTime($year.'-'.(3*$quarter-2).'-1 00:00:00');
        $end = new DateTime($year.'-'.(3*$quarter).'-'.($quarter == 1 || $quarter == 4 ? 31 : 30) .' 23:59:59');

        return array(
            'start' => $format ? $start->format($format) : $start,
            'end' => $format ? $end->format($format) : $end,
        );
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
            $query = $enlistModel->getEnlistmentById($enlistmentID);
            $folderCreation = XenForo_Application::get('options')->enableFolderCreation;
            $message = "";
            
            switch($rrdOption)
            {
                case '1':
                    // Name Change - taken
                    $message = '[B]Please select a different name. Your name has been already taken.[/B]';
                    $action = 'Name Change - taken';
                    $this->writeLog($enlistmentID, $action);
                    $phrase = new XenForo_Phrase('Message Sent');
                    break;
                case '2':
                    // Name Change - inappropriate
                    $message = '[B]Please select a different name. Your name is inappropriate.[/B]';
                    $action = 'Name Change - inappropriate';
                    $this->writeLog($enlistmentID, $action);
                    $phrase = new XenForo_Phrase('Message Sent');
                    break;
                case '3':
                    // Steam ID
                    $message = '[B]Please provide your steam ID for checking.[/B]';
                    $action = 'Steam ID';
                    $this->writeLog($enlistmentID, $action);
                    $phrase = new XenForo_Phrase('Message Sent');
                    break;
                case '4':
                    // Approved
                    $message = '[B]Application approved.[/B]';
                    $currentStatus = 2;
                    $this->updateEnlistmentsData($enlistmentID, $currentStatus);
                    $this->updateThread($query['thread_id'], $this->buildTitle($enlistmentID));
                    if($folderCreation) {
                        $rtcThreadID = $this->createThread(XenForo_Application::get('options')->rtcFolderForumID, $this->createRTCFolderTitle($enlistmentID), $this->createRTCFolderContent($enlistmentID));
                        $this->setRTCThreadID($enlistmentID,$rtcThreadID);
                        $steamContent = $this->getSteamContent($query['steamID']);
                        $s2ThreadID = $this->createThread(XenForo_Application::get('options')->s2FolderForumID, $this->createS2FolderTitle($enlistmentID), $this->createS2FolderContent($enlistmentID, $steamContent));
                        $this->setS2ThreadID($enlistmentID,$s2ThreadID);
                    }
                    $action = 'Approved';
                    $this->writeLog($enlistmentID, $action);
                    $phrase = new XenForo_Phrase('Application Approved');
                    break;
                case '5':
                    // Denied - Timed out
                    $message = '[B]Application timed out. Enlistment denied[/B]';
                    $currentStatus = 1;
                    $this->updateEnlistmentsData($enlistmentID, $currentStatus);
                    $this->updateThread($query['thread_id'], $this->buildTitle($enlistmentID));
                    $action = 'Denied - Timed out';
                    $this->writeLog($enlistmentID, $action);
                    $phrase = new XenForo_Phrase('Application Denied');
                    break;
                case '6':
                    // Denied
                    $message = '[B]Enlistment denied.[/B]';
                    $currentStatus = 1;
                    $this->updateEnlistmentsData($enlistmentID, $currentStatus);
                    $this->updateThread($query['thread_id'], $this->buildTitle($enlistmentID));
                    $action = 'Denied';
                    $this->writeLog($enlistmentID, $action);
                    $phrase = new XenForo_Phrase('Application Denied');
                    break;
                case '7':
                    // Moved
                    $message = '[B]Application sorted[/B]';
                    $this->sortApplication($enlistmentID, $query['thread_id'], $query['current_status']);
                    $phrase = new XenForo_Phrase('Application Sorted');
                    break;
            }
            $this->createPost($query['thread_id'], $message);
        }

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('enlistments'),
            $phrase
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
        $writer->set('thread_id', $threadId);
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

    public function createRTCFolderTitle($enlistmentID)
    {
        $enlistModel = $this->_getEnlistmentModel();
        $query = $enlistModel->getEnlistmentById($enlistmentID);
        $rank = "RTC";
        return $title = $rank . " " . $query['last_name'] . "." . $query['first_name'] . " | UNASSIGNED | " . $query['game'];
    }

    public function createRTCFolderContent($enlistmentID)
    {
        $enlistModel = $this->_getEnlistmentModel();
        $query = $enlistModel->getEnlistmentById($enlistmentID);
        $content = "";
        $newLine = "\n";
        
        $generalInformation = "[Size=6][B]General Information[/B][/Size]";
        if ($query['reenlistment'])
        {
            $reenlistment = "[B]Re-enlistment:[/B] Yes";
        } else {
            $reenlistment = "[B]Re-enlistment:[/B] No";
        }

        $dateApproved = "[B]Date Approved:[/B] " . date('dMy', XenForo_Application::$time);
        $home = XenForo_Application::get('options')->homeURL;
        $threadURL = '[URL="http://' .$home.'/thread/'.$query['thread_id'].'"]'. 'Enlistment #' .$query['enlistment_id'].'[/URL]';
        $thread = '[B]Enlistment Thread:[/B] ' . $threadURL;
        $steamID = "[B]Steam 64-bit ID:[/B] " . $query['steamID'];
        $bootCamp = "[B]Boot Camp Class Assigned[/B]: " . date('W/m/o');
        $timeZone = "[B]Time Zone:[/B] " . $query['timezone'];

        // Standard text
        $bootcampInfo = "[Size=6][B]Bootcamp Information[/B][/Size]";
        $bootCampDate = "[B]Date of Bootcamp:[/B]";
        $leadDI = "[B]Lead DI [RANK.Last.F]:[/B]";
        $attending = "[B]Attending DI [RANK.Last.F]:[/B]";
        $cadre = "[B]Cadre [RANK.Last.F]:[/B]";
        $scores = "[Size=6][B]Scores[/B][/Size]";
        $oral = "[B]Oral Test Scores [X/10]:[/B]";
        $targets1 = "[B]Time Stationary Targets [Rounds/MAX/X.XX sec]:[/B]";
        $targets2 = "[B]Result Moving targets [X/X]:[/B]";
        $targets3 = "[B]Shoot House Time [X.XX sec]:[/B]";
        $targets4 = "[B]Rifle Range Course Qualification [EX/SS/MM]:[/B]";
        $targets5 = "[B]Grenade Course Qualification [EX/SS/MM]:[/B]";
        $targets6 = "[B]Tactical/Obstacle Training Completed [Y - X.XX sec]:[/B]";
        $survey = "[B]Survey Sent [Y/N - LASTNAME]:[/B]";
        $check = "[B]S2 - Security Check Status:[/B]";
        $notes = "[B]Notes:[/B]";

        $folderContent = $bootcampInfo . $newLine . $bootCampDate . $newLine . $leadDI . $newLine . $attending . $newLine . $cadre .
                        $newLine . $newLine. $scores . $newLine . $oral . $newLine . $targets1 . $newLine .$targets2 . $newLine .
                        $targets3 . $newLine . $targets4 . $newLine. $targets5 . $newLine . $targets6 .
                        $newLine . $newLine . $survey . $newLine . $newLine . $check;

        return $content = $generalInformation . $newLine . $reenlistment . $newLine . $dateApproved . $newLine . $thread . $newLine . $steamID .
            $newLine . $bootCamp . $newLine . $timeZone . $newLine .$newLine . $folderContent . $newLine . $newLine . $notes;
    }

    public function setRTCThreadID($enlistmentID, $threadID)
    {
        $dw = XenForo_DataWriter::create('CavTools_DataWriter_Enlistments');
        $dw->setExistingData($enlistmentID);
        $dw->set('rtc_thread_id', $threadID);
        $dw->save();
    }

    public function getSteamContent($steamID)
    {

        // Player summary - http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=$key&steamids=$id
        // Player groups - http://api.steampowered.com/ISteamUser/GetUserGroupList/v0001/?key=$key&steamid=$id
        // Group names -  http://steamcommunity.com/gid/$gID/memberslistxml/?xml=1
        $key = XenForo_Application::get('options')->steamAPIKey;

        // Do player summary
        $url   = sprintf("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=%s&steamids=%s", $key, $steamID);
        //Send curl message
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $reply = curl_exec($ch);
        curl_close($ch);

        $reply = json_decode($reply, true);

        $name = $reply['response']['players'][0]['personaname'];
        $profile = $reply['response']['players'][0]['profileurl'];

        try {
            if ($reply['response']['players'][0]['communityvisibilitystate'] == 1) {
                $status = 1;
            } else if ($reply['response']['players'][0]['communityvisibilitystate'] == 3) {
                $status = 2;
            }
        }catch (Exception $e) {
            $status = 3;
        }

        return array('id' => $steamID, 'name' => $name, 
            'status' => $status, 'profile_url' => $profile);
    }

    public function createS2FolderTitle($enlistmentID)
    {
        $enlistModel = $this->_getEnlistmentModel();
        $query = $enlistModel->getEnlistmentById($enlistmentID);
        $rank = "RTC";
        return $title = $rank . " " . $query['last_name'] . "." . $query['first_name'] . " | UNASSIGNED";
    }

    public function createS2FolderContent($enlistmentID, $steamContent)
    {
        $enlistModel = $this->_getEnlistmentModel();
        $query = $enlistModel->getEnlistmentById($enlistmentID);
        $content = "";
        $newLine = "\n";
        $userDetails = $enlistModel->userDetails($query['user_id']);
        $home = XenForo_Application::get('options')->homeURL;
        
        $general = "[Size=6][B]General Information[/B][/Size]";
        $username = "[b]Username: [/b]" . '[URL="http://' .$home.'/members/'.$query['user_id'].'"]'. $userDetails['username']. '[/URL]';
        $enlistedName = "[B]Enlisted Name:[/B] ". $query['first_name'] . ", ". $query['last_name'];

        if ($query['reenlistment'])
        {
            $reenlistment = "[B]Re-enlistment:[/B] Yes";
        } else {
            $reenlistment = "[B]Re-enlistment:[/B] No";
        }
        
        $aliases = "[B]Aliases:[/B] ";
        $ip = "[B]IP Addresses:[/B] ";
        $email = "[B]Email address:[/B] " . $userDetails['email'];
        $rtcThreadURL = '[URL="http://' .$home.'/thread/'.$query['thread_id'].'"]'. 'RTC Folder'.'[/URL]';
        $rtcThread = '[B]RTC Folder:[/B] ' . $rtcThreadURL;
        $threadURL = '[URL="http://' .$home.'/threads/'.$query['thread_id'].'"]'. 'Enlistment #' .$query['enlistment_id']. '[/URL]';
        $thread = '[B]Enlistment Thread:[/B] ' . $threadURL;
        $steam = "[Size=6][B]Steam Review-Cleared/Hold[/B][/Size]";
        $steamName = "[B]Username:[/B] " . $steamContent['name'];

        if ($steamContent['status'] = 1) {
            $steamStatus = "[B]Status:[/B] [Color=red]Private[/Color]";
        } else if ($steamContent['status'] = 2) {
            $steamStatus = "[B]Status:[/B] [Color=green]Public[/Color]";
        } else if ($steamContent['status'] = 3) {
            $steamStatus = "[B]Status:[/B] [Color=yellow]Unknown[/Color]";
        }

        $steamID = "[B]ID:[/B] " . $steamContent['id'];
        $steamURL = '[URL="' . $steamContent['profile_url'] . '"]Profile[/URL]';
        $steamLink = "[B]Account link:[/B] " . $steamURL;
        $steamGroups = "[B]Groups:[/B] ";
        $steamAliases = "[B]Aliases:[/B] ";

        $info = "[B]Additional Information:[/B] ";
        $echelon  = "[Size=6][B]Echelon Review Status-Cleared/Hold[/B][/Size]";
        $echelonName = "[B]Name:[/B]";
        $echelonIP = "[B]IP Address:[/B]";
        $echelonID = "[B]Client ID:[/B]";
        $echelonCons = "[B]Connections:[/B]";
        $echelonWarn = "[B]# of Warnings:[/B]";
        $echelonWarnFor = "Warnings for:[/B]";
        $echelonBans = "[B]# of Temp Bans:[/B]";
        $echelonBansFor = "Temp Bans for:[/B]";
        $echelonAdd = "[B]Additional Information[/B]";
        $echelonContent = $echelonName . $newLine . $echelonIP . $newLine . $echelonID . $newLine .
            $echelonCons . $newLine . $echelonWarn . $newLine . $echelonWarnFor . $newLine . $echelonBans .
            $newLine . $echelonBansFor . $newLine . $echelonAdd;
        $misc = "[B]Miscellaneous-[/B]";
        $summary = "[B]Summary -[/B]";

        return $content = $general . $newLine . $username . $newLine. $enlistedName . $newLine . $reenlistment . $newLine . $aliases . $newLine .
            $email . $newLine . $ip . $newLine .  $rtcThread . $newLine . $thread . $newLine . $newLine . $steam . $newLine .  $steamName . 
            $newLine . $steamStatus . $newLine . $steamID . $newLine . $steamLink . $newLine . $steamGroups . $newLine . $steamAliases . $newLine .
            $info . $newLine . $newLine . $echelon . $newLine . $echelonContent . $newLine . $newLine . $misc . $newLine .
            $newLine . $summary;
    }

    public function setS2ThreadID($enlistmentID, $threadID)
    {
        $dw = XenForo_DataWriter::create('CavTools_DataWriter_Enlistments');
        $dw->setExistingData($enlistmentID);
        $dw->set('s2_thread_id', $threadID);
        $dw->save();
    }

    public function createThread($forumID, $title, $message)
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

    protected function _getEnlistmentModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_Enlistment' );
    }
}