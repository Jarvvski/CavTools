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
                $cavname = "";
                $cavName = $lastName . "." . $firstName[0];
                $nameCheck = $this->checkName($cavName,$enlistment['user_id']);

                switch ($enlistment['current_status'])
                {
                    case 1: $status = '<td><div id="red">Denied</div></td>';break;
                    case 2: $status = '<td><div id="green">Approved</div></td>';break;
                    case 3: $status = '<td><div id="yellow">Open</div></td>';break;
                }


                if ($daysSince > 7) {
                    $dayStatus = '<td><div id="red">';
                } else {
                    $dayStatus = '<td><div>';
                }

                switch($nameCheck)
                {
                    case 1: $nameStatus = '<td><div id="red">';break;
                    case 2: $nameStatus = '<td><div id="green">';break;
                    case 3: $nameStatus = '<td><div id="yellow">';break;
                }

                switch ($enlistment['vac_ban']) {
                    case 1: $banStatus = '<td nowrap><div id="red">';break;
                    case 2: $banStatus = '<td nowrap><div id="green">';break;
                    case 3: $banStatus = '<td nowrap><div id="yellow">';break;
                }

                if ($underage) {
                    $ageStatus = '<td><div id="red">';
                } else {
                    $ageStatus = '<td><div>';
                }
                if ($canAction) {
                    if (!$reenlistment) {
                        if (count($enlistments) != 0) {
                            $normalEnlistments .= "<tr><td><a href=" . $thread . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . date('m-d-y', $enlistment['enlistment_date']) . "</td>$dayStatus" . $daysSince . "</div></td>$nameStatus" . htmlspecialchars($cavName) . "</div></td><td>". htmlspecialchars($enlistment['first_name']) . "</td><td>" . htmlspecialchars($enlistment['recruiter']) . "</td>$banStatus " . htmlspecialchars($enlistment['steamID']) . "</div></td>$ageStatus" . htmlspecialchars($enlistment['age']) . "</div></td>$status<td><input type=\"checkbox\" name=\"enlistments[]\" value=" . $enlistment['enlistment_id'] . "></td></tr>" . PHP_EOL;
                        }
                    }

                    if ($reenlistment) {
                        if (count($enlistments) != 0) {
                            $reEnlistments .= "<tr><td><a href=" . $thread . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . date('m-d-y', $enlistment['enlistment_date']) . "</td>$dayStatus" . $daysSince . "</div></td>$nameStatus" . htmlspecialchars($cavName) . "</div></td><td>". htmlspecialchars($enlistment['first_name']) . "</td><td>" . htmlspecialchars($enlistment['recruiter']) . "</td>$banStatus" . htmlspecialchars($enlistment['steamID']) . "</div></td>$ageStatus" . htmlspecialchars($enlistment['age']) . "</div></td>$status<td><input type=\"checkbox\" name=\"enlistments[]\" value=" . $enlistment['enlistment_id'] . "></td></tr>" . PHP_EOL;
                        }
                    }
                } else {
                    if (!$reenlistment) {
                        if (count($enlistments) != 0) {
                            $normalEnlistments .= "<tr><td><a href=" . $thread . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . date('m-d-y', $enlistment['enlistment_date']) . "</td>$dayStatus" . $daysSince . "</div></td>$nameStatus" . htmlspecialchars($cavName) . "</div></td><td>". htmlspecialchars($enlistment['first_name']) . "</td><td>" . htmlspecialchars($enlistment['recruiter']) . "</td>$banStatus " . htmlspecialchars($enlistment['steamID']) . "</div></td>$ageStatus" . htmlspecialchars($enlistment['age']) . "</div></td>$status</tr>" . PHP_EOL;
                        }
                    }

                    if ($reenlistment) {
                        if (count($enlistments) != 0) {
                            $reEnlistments .= "<tr><td><a href=" . $thread . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . date('m-d-y', $enlistment['enlistment_date']) . "</td>$dayStatus" . $daysSince . "</div></td>$nameStatus" . htmlspecialchars($cavName) . "</div></td><td>". htmlspecialchars($enlistment['first_name']) . "</td><td>" . htmlspecialchars($enlistment['recruiter']) . "</td>$banStatus" . htmlspecialchars($enlistment['steamID']) . "</div></td>$ageStatus" . htmlspecialchars($enlistment['age']) . "</div></td>$status</tr>" . PHP_EOL;
                        }
                    }
                }
            }
        }

        $quarter = $this->getDatesOfQuarter();
        $prevQuarter = $this->getDatesOfQuarter('previous');
        $year = $this->getDatesOfYear();
        $prevYear = $this->getDatesOfYear('previous');

        $games = XenForo_Application::get('options')->games;
        $recruiterIDs = XenForo_Application::get('options')->recruiterIDs;
        $games = explode(',', $games);
        $recruiterIDs = explode(',', $recruiterIDs);
        $recruiters = $enlistModel->getAllRecruiters($recruiterIDs);
        $gameData = array();

        foreach ($games as $game) {

            $currQuarterData = $enlistModel->getEnlistmentsForPeriod($quarter['start'], $quarter['end'], $game);
            $prevQuarterData = $enlistModel->getEnlistmentsForPeriod($prevQuarter['start'], $prevQuarter['end'], $game);

            $currYearData = $enlistModel->getEnlistmentsForPeriod($year['start'], $year['end'], $game);
            $prevYearData = $enlistModel->getEnlistmentsForPeriod($prevYear['start'], $prevYear['end'], $game);

            $monthlyData =array();
            for($i=1;$i<13;$i++)
            {
                $monthNum  = $i;
                $dateObj   = DateTime::createFromFormat('!m', $monthNum);
                $monthName = $dateObj->format('F');

                $monthTime = $this->getDatesOfMonth($monthName);

                $count = $enlistModel->getEnlistmentsForPeriod($monthTime['start'], $monthTime['end'], $game);
                array_push($monthlyData, $count);
            }


            $data = array(
                'title' => $game,
                'prev_quart_enlist' => $prevQuarterData,
                'curr_quart_enlist' => $currQuarterData,
                'prev_year_enlist' => $prevYearData,
                'curr_year_enlist' => $currYearData,
                'monthly' => $monthlyData
            );
            array_push($gameData, $data);
        }

        $monthList = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

        $totalRecruiterData = array();

        foreach ($monthList as $mon) {
            $arrData = array();
            foreach ($recruiters as $recruiter) {
                $monthName  = $mon;
                $monthTime = $this->getDatesOfMonth($monthName);

                $recruitedThisYear = $enlistModel->getRecruitingForPeriod($monthTime['start'], $monthTime['end'], $recruiter);
                $recruiterData = array(
                    'username' => $recruiter,
                    'count' => $recruitedThisYear
                );
                array_push($arrData, $recruiterData);
            }
            array_push($totalRecruiterData, $arrData);
        }

        $janRecruiterData = $totalRecruiterData[0];
        $febRecruiterData = $totalRecruiterData[1];
        $marRecruiterData = $totalRecruiterData[2];
        $aprRecruiterData = $totalRecruiterData[3];
        $mayRecruiterData = $totalRecruiterData[4];
        $junRecruiterData = $totalRecruiterData[5];
        $julRecruiterData = $totalRecruiterData[6];
        $augRecruiterData = $totalRecruiterData[7];
        $sepRecruiterData = $totalRecruiterData[8];
        $octRecruiterData = $totalRecruiterData[9];
        $novRecruiterData = $totalRecruiterData[10];
        $decRecruiterData = $totalRecruiterData[11];



        $body = "";
        for ($i=0;$i<count($monthList);$i++) {
            $body .= "<tr>";
            $body .= "<th>".$monthList[$i]."</th>";
            foreach ($gameData as $game) {
                $body .= "<td>".$game['monthly'][$i]."</td>";
            }
            $body .= "</tr>";
        }

        //View Parameters
        $viewParams = array(
            'janRecruiterData' => $janRecruiterData,
            'febRecruiterData' => $febRecruiterData,
            'marRecruiterData' => $marRecruiterData,
            'aprRecruiterData' => $aprRecruiterData,
            'mayRecruiterData' => $mayRecruiterData,
            'junRecruiterData' => $junRecruiterData,
            'julRecruiterData' => $julRecruiterData,
            'augRecruiterData' => $augRecruiterData,
            'sepRecruiterData' => $sepRecruiterData,
            'octRecruiterData' => $octRecruiterData,
            'novRecruiterData' => $novRecruiterData,
            'decRecruiterData' => $decRecruiterData,
            'totalGameData' => $gameData,
            'tableBody' => $body,
            'normalEnlistments' => $normalEnlistments,
            'reEnlistments' => $reEnlistments,
            'canMajorAction' => $canMajorAction,
            'canAction' => $canAction
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_EnlistmentManagement', 'CavTools_EnlistmentManagement', $viewParams);
    }

    /**
     * Compute the start and end date of some fixed o relative quarter in a specific year.
     * @param mixed $quarter  Integer from 1 to 4 or relative string value:
     *                        'this', 'current', 'previous', 'first' or 'last'.
     *                        'this' is equivalent to 'current'. Any other value
     *                        will be ignored and instead current quarter will be used.
     *                        Default value 'current'. Particularly, 'previous' value
     *                        only make sense with current year so if you use it with
     *                        other year like: get_dates_of_quarter('previous', 1990)
     *                        the year will be ignored and instead the current year
     *                        will be used.
     * @param int $year       Year of the quarter. Any wrong value will be ignored and
     *                        instead the current year will be used.
     *                        Default value null (current year).
     * @param string $format  String to format returned dates
     * @return array          Array with two elements (keys): start and end date.
     */
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
            'end' => $format ? $end->format($format) : $end
        );
    }

    public function getDatesOfYear($year = null, $format = null)
    {
        if (!is_int($format)) {
            $format = 'U';
        }

        if ( !is_string($year)) {
            $year = (new DateTime)->format('Y');
        } else {
            switch ($year) {
                case 'current':
                    $year = (new DateTime)->format('Y');
                    break;
                case 'previous':
                    $year = (new DateTime)->format('Y');
                    $year--;
                    break;
            }
        }

        $start = new DateTime('first day of January '.$year.' 00:00:00');
        $end = new DateTime('last day of December '.$year.' 23:59:59');

        return array(
            'start' => $format ? $start->format($format) : $start,
            'end' => $format ? $end->format($format) : $end
        );
    }

    public function getDatesOfMonth($month)
    {
        $year = (new DateTime)->format('Y');
        $format = 'U';
        $start = new DateTime('first day of '.$month .' '.$year.' 00:00:00');
        $end = new DateTime('last day of '.$month.' '.$year.' 23:59:59');

        return array(
            'start' => $format ? $start->format($format) : $start,
            'end' => $format ? $end->format($format) : $end
        );
    }

    public function actionPost()
    {

        // get the user_id from the user
        $visitor  = XenForo_Visitor::getInstance()->toArray();

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
                    $nameTaken = XenForo_Application::get('options')->nameTaken;
                    $message = '[B]'.$nameTaken.'[/B]';
                    // $message = '[B]Please select a different name. Your name has been already taken.[/B]';
                    $action = 'Name Change - taken';
                    $this->writeLog($enlistmentID, $action);
                    $phrase = new XenForo_Phrase('Message Sent');
                    break;
                case '2':
                    // Name Change - inappropriate
                    $nameInap = XenForo_Application::get('options')->nameInap;
                    $message = '[B]'.$nameInap.'[/B]';
                    // $message = '[B]Please select a different name. Your name is inappropriate.[/B]';
                    $action = 'Name Change - inappropriate';
                    $this->writeLog($enlistmentID, $action);
                    $phrase = new XenForo_Phrase('Message Sent');
                    break;
                case '3':
                    // Steam ID
                    $needSteam = XenForo_Application::get('options')->needSteam;
                    $message = '[B]'.$needSteam.'[/B]';
                    // $message = '[B]Please provide your steam ID for checking.[/B]';
                    $action = 'Steam ID';
                    $this->writeLog($enlistmentID, $action);
                    $phrase = new XenForo_Phrase('Message Sent');
                    break;
                case '4':
                    // Approved
                    $message = $this->approvalMessage($enlistmentID);
                    $currentStatus = 2;
                    $this->updateEnlistmentsData($enlistmentID, $currentStatus);
                    $this->updateThread($query['thread_id'], $this->buildTitle($enlistmentID));
                    if ($query['reenlistment'] === 0) {
                        if ($folderCreation) {
                            $rtcThreadID = $this->createThread(XenForo_Application::get('options')->rtcFolderForumID, $this->createRTCFolderTitle($enlistmentID), $this->createRTCFolderContent($enlistmentID));
                            $this->setRTCThreadID($enlistmentID, $rtcThreadID);
                            $steamContent = $this->getSteamContent($query['steamID']);
                            $s2ThreadID = $this->createThread(XenForo_Application::get('options')->s2FolderForumID, $this->createS2FolderTitle($enlistmentID), $this->createS2FolderContent($enlistmentID, $steamContent));
                            $this->setS2ThreadID($enlistmentID, $s2ThreadID);
                        }
                    }
                    $action = 'Approved';
                    $this->writeLog($enlistmentID, $action);
                    $phrase = new XenForo_Phrase('Application Approved');
                    break;
                case '5':
                    // Denied - Timed out
                    $timeOut = XenForo_Application::get('options')->timeOut;
                    $message = '[B]'.$timeOut.'[/B]';
                    // $message = '[B]Application timed out. Enlistment denied[/B]';
                    $currentStatus = 1;
                    $this->updateEnlistmentsData($enlistmentID, $currentStatus);
                    $this->updateThread($query['thread_id'], $this->buildTitle($enlistmentID));
                    $action = 'Denied - Timed out';
                    $this->writeLog($enlistmentID, $action);
                    $phrase = new XenForo_Phrase('Application Denied');
                    break;
                case '6':
                    // Denied
                    $deniedMsg = XenForo_Application::get('options')->deniedMsg;
                    $message = '[B]'.$deniedMsg.'[/B]';
                    // $message = '[B]Enlistment denied.[/B]';
                    $currentStatus = 1;
                    $this->updateEnlistmentsData($enlistmentID, $currentStatus);
                    $this->updateThread($query['thread_id'], $this->buildTitle($enlistmentID));
                    $action = 'Denied';
                    $this->writeLog($enlistmentID, $action);
                    $phrase = new XenForo_Phrase('Application Denied');
                    break;
                case '7':
                    // Moved
                    $sortedMsg = XenForo_Application::get('options')->sortedMsg;
                    $message = '[B]'.$sortedMsg.'[/B]';
                    // $message = '[B]Application sorted[/B]';
                    $this->sortApplication($enlistmentID, $query['thread_id'], $query['current_status']);
                    $phrase = new XenForo_Phrase('Application Sorted');
                    break;
            }
            $this->createPost($query['thread_id'], $message);
        }

        $this->tweet($visitor);

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
        $rank = "RCT";
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
        $threadURL = '[URL="http://' .$home.'/threads/'.$query['thread_id'].'"]'. 'Enlistment #' .$query['enlistment_id'].'[/URL]';
        $thread = '[B]Enlistment Thread:[/B] ' . $threadURL;
        $steamID = "[B]Steam 64-bit ID:[/B] " . $query['steamID'];
        $originID = "[B]Origin ID:[/B] " . $query['origin'];
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
            $newLine . $originID . $newLine . $bootCamp . $newLine . $timeZone . $newLine .$newLine . $folderContent . $newLine . $newLine . $notes;
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

        try {
            $name = $reply['response']['players'][0]['personaname'];
            $profile = $reply['response']['players'][0]['profileurl'];
        } catch (Exception $e) {
            $name = "Incorrect SteamID given";
            $profile = "Incorrect SteamID given";
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

        return array('id' => $steamID, 'name' => $name,
            'status' => $status, 'profile_url' => $profile);
    }

    public function createS2FolderTitle($enlistmentID)
    {
        $enlistModel = $this->_getEnlistmentModel();
        $query = $enlistModel->getEnlistmentById($enlistmentID);
        $rank = "RCT";
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
        $originID = "[B]Origin ID:[/B] " . $query['origin'];
        $age = "[B]Age:[/b] " . $query['age'];
        $rtcThreadURL = '[URL="http://' .$home.'/threads/'.$query['rtc_thread_id'].'"]'. 'RTC Folder'.'[/URL]';
        $rtcThread = '[B]RTC Folder:[/B] ' . $rtcThreadURL;
        $threadURL = '[URL="http://' .$home.'/threads/'.$query['thread_id'].'"]'. 'Enlistment #' .$query['enlistment_id']. '[/URL]';
        $thread = '[B]Enlistment Thread:[/B] ' . $threadURL;
        $steam = "[Size=6][B]Steam Review-Cleared/Hold[/B][/Size]";
        $steamName = "[B]Username:[/B] '" . $steamContent['name'] . "'";

        if ($steamContent['status'] == 1) {
            $steamStatus = "[B]Status:[/B] [Color=red]Private[/Color]";
        } else if ($steamContent['status'] == 2) {
            $steamStatus = "[B]Status:[/B] [Color=green]Public[/Color]";
        } else if ($steamContent['status'] == 3) {
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
        $echelonWarnFor = "Warnings for:";
        $echelonBans = "[B]# of Temp Bans:[/B]";
        $echelonBansFor = "Temp Bans for:";
        $echelonAdd = "[B]Additional Information[/B]";
        $echelonContent = $echelonName . $newLine . $echelonIP . $newLine . $echelonID . $newLine .
            $echelonCons . $newLine . $echelonWarn . $newLine . $echelonWarnFor . $newLine . $echelonBans .
            $newLine . $echelonBansFor . $newLine . $echelonAdd;
        $misc = "[B]Miscellaneous-[/B]";
        $summary = "[B]Summary -[/B]";

        return $content = $general . $newLine . $username . $newLine. $enlistedName . $newLine . $reenlistment . $newLine . $aliases . $newLine .
            $email . $newLine . $originID . $newLine . $age . $newLine . $ip . $newLine .  $rtcThread . $newLine . $thread . $newLine . $newLine . $steam .
            $newLine .  $steamName . $newLine . $steamStatus . $newLine . $steamID . $newLine . $steamLink . $newLine . $steamGroups . $newLine . $steamAliases .
            $newLine . $info . $newLine . $newLine . $echelon . $newLine . $echelonContent . $newLine . $newLine . $misc . $newLine .
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
        $writer->set('sticky', true);
        $writer->preSave();
        $writer->save();
        return $writer->getDiscussionId();
    }

    public function approvalMessage($enlistmentID)
    {
        $enlistModel = $this->_getEnlistmentModel();
        $query = $enlistModel->getEnlistmentById($enlistmentID);
        $firstName = ucwords($query['first_name']);
        $lastName = ucwords($query['last_name']);
        $cavName = '';
        $cavName = $lastName . "." . $firstName[0];
        $newLine = "\n";

        $header = "Please change your dog tags to [B]RCT.".$cavName."[/B] and TeamSpeak to [B]".$cavName."[/B]".$newLine.
            "A Drill Instructor will contact you shortly.".$newLine.$newLine."[B]Welcome to the Brotherhood of the Yellow and Black;
            battle tested and forged in the fires of hell itself![/B]";

        $quote = "[COLOR=#FF0000]“As a new trooper we will forge you into that individual. We will give more than gamemanship. We will develop people skills,
        positive interaction with others. Guide you into giving of yourself for the good of others and putting yourself second. We will calm your
        tempers, give you purpose that will develop you into an individual that will be an asset in your daily life outside the Cavalry.
        There are many here that have advanced age that has dealt with life's many challenges. All will help with advice and encouragement,
        if you will put forth a contact. No one lives alone and can only grow in stature as a human being with positive reaction with others of equal
        nobility and honor in what they do.“".$newLine."CSM.Cold.R[/COLOR]";

        $teamspeak = "TeamSpeak IP: ts3.7Cav.us".$newLine."TeamSpeak PW: 7thCavalry";

        $notes = "[B]**NOTE**[/B]".$newLine."S1 and DI's please note".$newLine.$newLine."First Name: ".$firstName.$newLine."Last Name: ".$lastName.
            $newLine.$newLine;

        return $message = $header.$newLine.$newLine.$quote.$newLine.$newLine.$teamspeak.$newLine.$newLine.$notes.$newLine.$newLine;
    }

    protected function _getEnlistmentModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_Enlistment' );
    }

    public function tweet($visitor)
    {
        $text = $visitor['username'] . " handing the enlistments like a pro!";
        $hashtag = "#Proud #7Cav #IMO";

        $twitterModel = $this->_getTwitterBot();
        $twitterModel->postStatus($text, $hashtag);
    }

    protected function _getTwitterBot()
    {
        return $this->getModelFromCache( 'CavTools_Model_IMOBot' );
    }
}
