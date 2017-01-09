<?PHP

class CavTools_CronJobs_S2QuartCheck {
    // loop through steam profiles
    // return VAC banned profiles
    // return names which include select words
    // return 25% sample to a forum post in given S2 forum

    // OPTIONS NEEDED
    // $S2OIC = XenForo_Application::get('options')->S2OICuserID;
    // $S2XO = XenForo_Application::get('options')->S2XOuserID;
    // $S2NCOIC = XenForo_Application::get('options')->S2NCOICuserID;
    // $forumID    = XenForo_Application::get('options')->S2CheckForumID;

    public static function getPoster() {
        //Get values from options
        $userID = XenForo_Application::get('options')->enlistmentPosterID;

        $db = XenForo_Application::get('db');
        $botUsername = $db->fetchRow("
                            SELECT username
                           FROM xf_user
                           WHERE user_id = " . $userID . "
                       ");

        $username = $botUsername['username'];
        $botVars = array(
            "user_id" => $userID,
            "username" => $username
        );
        return $botVars;
    }

    public static function getSteamProfiles() {
        $db = XenForo_Application::get('db');
        return $db->fetchAll("
            SELECT t1.user_id, t1.field_value, t2.username, t2.relation_id, t3.title
            FROM xf_user_field_value t1
            INNER JOIN xf_pe_roster_user_relation t2
            ON t1.user_id = t2.user_id
            INNER JOIN xf_pe_roster_rank t3
            ON t2.rank_id = t3.rank_id
            WHERE field_id = 'armaGUID'
        ");
    }

    public static function mainLoop() {

        $month = date('n');

        if ($month == 1 || $month == 4 || $month == 7 || $month == 10) {

            $steamProfiles = self::getSteamProfiles();

            $goodProfiles = array();
            $badProfiles = array();

            foreach ($steamProfiles as $profile) {
                if ($profile['field_value'] == '') {
                    array_push($badProfiles, $profile);
                } else {
                    array_push($goodProfiles, $profile);
                }
            }

            foreach ($goodProfiles as $index => $data) {


                //Set variables
                // $key   = XenForo_Application::get('options')->steamAPIKey;
                $key = "E1ABFA96B4D9917DF7478A070643C062";
                $profile = array();
                $profile['user_id'] = $data['user_id'];
                $profile['username'] = $data['username'];
                $profile['field_value'] = $goodProfiles[$index]['field_value'];
                $id = $data['field_value'];
                $profile['relation_id'] = $data['relation_id'];
                $profile['title'] = $data['title'];

                $url   = sprintf("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=%s&steamids=%s", $key, $id);

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
                    $state   = $reply['response']['players'][0]['personastate'];
                    $avatar  = $reply['response']['players'][0]['avatarmedium'];
                    $url     = $reply['response']['players'][0]['profileurl'];
                    $vis     = $reply['response']['players'][0]['communityvisibilitystate'];
                } catch (Exception $e) {
                    $name    = "Invalid SteamID given";
                    $state   = 7;
                    $avatar  = "http://placehold.it/64x64";
                    $url     = '#';
                    $vis     = "Invalid SteamID given";
                }

                switch ($vis)
                {
                    case 1: $vis = '[COLOR="red"]Private[/COLOR]';break;
                    case 2: $vis = '[COLOR="red"]Private[/COLOR]';break;
                    case 3: $vis = '[COLOR="green"]Public[/COLOR]';break;
                    default: $vis = '[COLOR="yellow"]UNKOWN[/COLOR]';break;
                }


                switch ($state)
                {
                    case 0: $steamState = '[COLOR="red"]Offline[/COLOR]';break;
                    case 1:
                    case 4:
                    case 5:
                    case 6:
                            $steamState = '[COLOR="green"]Online[/COLOR]';break;
                    case 3:
                    case 2:
                            $steamState = '[COLOR="yellow"]Away[/COLOR]';break;
                    case 7: $steamState = '[COLOR="yellow"]Invalid SteamID given[/COLOR]';break;
                    default: $vis = '[COLOR="yellow"]UNKOWN[/COLOR]';break;
                }

                $profile['steam_vis'] = $vis;
                $profile['steam_username'] = $name;
                $profile['steam_state'] = $steamState;
                $profile['steam_avatar'] = $avatar;
                $profile['steam_url'] = $url;

                $url   = sprintf("http://api.steampowered.com/ISteamUser/GetPlayerBans/v1/?key=%s&steamids=%s", $key, $id);

                // Send curl message
                $ch  = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                $reply = curl_exec($ch);
                curl_close($ch);

                $reply = json_decode($reply, true);

                try {
                    $VACban = $reply['players'][0]['VACBanned'];
                    $VACnum = $reply['players'][0]['NumberOfVACBans'];
                    $communityBan = $reply['players'][0]['CommunityBanned'];
                    $gameBansNum = $reply['players'][0]['NumberOfGameBans'];
                    $econBan = $reply['players'][0]['EconomyBan'];
                } catch (Exception $e) {
                    $VACban = "Invalid SteamID given";
                    $VACnum = "Invalid SteamID given";
                    $communityBan = "Invalid SteamID given";
                    $gameBansNum = "Invalid SteamID given";
                    $econBan = "Invalid SteamID given";
                }

                $profile['VAC_status'] = $VACban;
                $profile['VAC_num'] = $VACnum;
                $profile['community_ban_status'] = $communityBan;
                $profile['game_ban_num'] = $gameBansNum;
                $profile['econ_ban'] = $econBan;

                $goodProfiles[$index] = $profile;

            }

            $thread = self::makeThread($goodProfiles, $badProfiles);
            self::alertS2HQ($goodProfiles, $badProfiles, $thread);
        }
    }

    public static function makeThread($goodProfiles, $badProfiles) {

        // set vars
        $poster     = self::getPoster();
        $forumID    = XenForo_Application::get('options')->S2CheckForumID;
        $count      = 0;
        $message    = "";
        $newline    = "\n";
        $milpacsURL = 'https://dev.7cav.us/rosters/profile?uniqueid=';
        $text       = '';

        foreach ($badProfiles as $profile) {

            $profileLink = "[B][URL=".$milpacsURL.$profile['relation_id']."]".$profile['username']."[/URL][/B]";

            $text .= $newline . $profileLink . "  --  [B][COLOR=YELLOW]STEAM ID NOT PROVIDED[/COLOR][/B]" . $newline . $newline;

        }

        foreach ($goodProfiles as $profile) {
            if ($profile['steam_url'] == '#') {
                $profileLink = "[B][URL=".$milpacsURL.$profile['relation_id']."]".$profile['username']."[/URL][/B]";

                $text .= $newline . $profileLink . "  --  [B][COLOR=YELLOW]INVALID STEAM ID[/COLOR][/B]" . $newline . $newline;
            }
        }

        if ($goodProfiles[0]['VAC_status'] == true || $goodProfiles[0]['community_ban_status'] == true || $goodProfiles[0]['econ_ban'] == "probation" || $goodProfiles[0]["game_ban_num"] > 0) {
            $text .= $newline . $newline . "[H][COLOR=RED]SUSPECTS[/COLOR][/H]" . $newline . $newline;
        }

        foreach ($goodProfiles as $profile) {
            if ($profile['steam_url'] != '#') {
                if ($profile['VAC_status'] == true || $profile['community_ban_status'] == true || $profile['econ_ban'] == "probation" || $profile["game_ban_num"] > 0) {
                    $milpacProfile = "[B][URL=".$milpacsURL.$profile['relation_id']."]".$profile['title']." ".$profile['username']."[/URL][/B]";

                    $steam      = "[IMG]".$profile['steam_avatar']."[/IMG]".$newline."[B]"."[URL=".$profile['steam_url']."]".$profile['steam_username']."[/URL]"."[/B]".$newline.
                                    "".$profile['steam_state'].$newline."[B]Visability:[/B] ".$profile['steam_vis'].$newline."[B]Steam ID: [/B]".$profile['field_value'];


                    if ($profile['VAC_status'] == false) {
                        $VACbanStatus = '[COLOR="green"]CLEAN[/COLOR]';
                    } else if ($profile['VAC_status'] == true) {
                        $VACbanStatus = '[COLOR="red"]BANNED[/COLOR]';
                    } else {
                        $VACbanStatus = '[COLOR="yellow"]UNKNOWN[/COLOR]';
                    }

                    if ($profile['community_ban_status'] == false) {
                        $communityBanStatus = '[COLOR="green"]CLEAN[/COLOR]';
                    } else if ($profile['community_ban_status'] == true) {
                        $communityBanStatus = '[COLOR="red"]BANNED[/COLOR]';
                    } else {
                        $communityBanStatus = '[COLOR="yellow"]UNKNOWN[/COLOR]';
                    }

                    $profileLink = "[B]Milpac Link: [/B]".$milpacProfile;
                    $VACban     = "[B]Vac Banned: [/B]".$VACbanStatus;
                    $VACnum      = "[B]Number of VAC bans: [/B]".$profile['VAC_num'];
                    $comBan      = "[B]Community Banned: [/B]".$communityBanStatus;
                    $gameBanNum  = "[B]Number of game bans: [/B]".$profile['game_ban_num'];
                    $econBan     = "[B]Economy Bans: [/B]".$profile['econ_ban'];
                    $text .= $newline . $steam  . $newline . $profileLink . $newline . $VACban . $newline . $VACnum . $newline . $comBan . $newline . $gameBanNum . $newline . $econBan . $newline;
                }
            }
        }

        $text .= $newline . $newline . "[H][COLOR=GREEN]RANDOM SPOT CHECK[/COLOR][/H]" . $newline . $newline;
        foreach ($goodProfiles as $profile) {

            if ($profile['steam_url'] != '#') {
                $randVal = rand(1,4);
                if ($randVal == 2) {

                    $milpacProfile = "[B][URL=".$milpacsURL.$profile['relation_id']."]".$profile['title']." ".$profile['username']."[/URL][/B]";

                    $steam      = "[IMG]".$profile['steam_avatar']."[/IMG]".$newline."[B]"."[URL=".$profile['steam_url']."]".$profile['steam_username']."[/URL]"."[/B]".$newline.
                                    "".$profile['steam_state'].$newline."[B]Visability:[/B] ".$profile['steam_vis'].$newline."[B]Steam ID: [/B]".$profile['field_value'];

                    if ($profile['VAC_status'] == false) {
                        $VACbanStatus = '[COLOR="green"]CLEAN[/COLOR]';
                    } else if ($profile['VAC_status'] == true) {
                        $VACbanStatus = '[COLOR="red"]BANNED[/COLOR]';
                    } else {
                        $VACbanStatus = '[COLOR="yellow"]UNKNOWN[/COLOR]';
                    }

                    if ($profile['community_ban_status'] == false) {
                        $communityBanStatus = '[COLOR="green"]CLEAN[/COLOR]';
                    } else if ($profile['community_ban_status'] == true) {
                        $communityBanStatus = '[COLOR="red"]BANNED[/COLOR]';
                    } else {
                        $communityBanStatus = '[COLOR="yellow"]UNKNOWN[/COLOR]';
                    }

                    $profileLink = "[B]Milpac Link: [/B]".$milpacProfile;
                    $VACban     = "[B]Vac Banned: [/B]".$VACbanStatus;
                    $VACnum      = "[B]Number of VAC bans: [/B]".$profile['VAC_num'];
                    $comBan      = "[B]Community Banned: [/B]".$communityBanStatus;
                    $gameBanNum  = "[B]Number of game bans: [/B]".$profile['game_ban_num'];
                    $econBan     = "[B]Economy Bans: [/B]".$profile['econ_ban'];
                    $text .= $newline . $steam . $newline . $profileLink . $newline . $VACban . $newline . $VACnum . $newline . $comBan . $newline . $gameBanNum . $newline . $econBan . $newline;
                }
            }
        }

        $eventDate = date('dMy'); // 13Jun2016
        $eventDate = strtoupper($eventDate); // 13JUN2016

        $title = "[S2] AutoCheck - ".$eventDate;

        // write the thread
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $writer->set('user_id', $poster['user_id']);
        $writer->set('username', $poster['username']);
        $writer->set('title', $title);
        $postWriter = $writer->getFirstMessageDw();
        $postWriter->set('message', $text);
        $writer->set('node_id', $forumID);
        $writer->preSave();
        $writer->save();
        return $writer->getDiscussionId();
    }

    public static function currentQuarter(){
         $n = date('n');
         if($n < 4){
              return "1";
         } elseif($n > 3 && $n <7){
              return "2";
         } elseif($n >6 && $n < 10){
              return "3";
         } elseif($n >9){
              return "4";
         }
    }

    public static function alertS2HQ($goodProfiles, $badProfiles, $threadID) {
        $poster = self::getPoster();
        $S2OIC = XenForo_Application::get('options')->S2OICuserID;
        $S2XO = XenForo_Application::get('options')->S2XOuserID;
        $S2NCOIC = XenForo_Application::get('options')->S2NCOICuserID;
        $text = "";

        $recipients = array($S2OIC, $S2XO, $S2NCOIC, $poster['user_id']);

        $eventDate = date('dMy'); // 13Jun2016
        $eventDate = strtoupper($eventDate); // 13JUN2016
        $newline = '\n';

        $title = "[S2] AutoCheck - ".$eventDate;

        $text = "[H]Quarter " .self::currentQuarter(). " check completed[/H]";

        $badProfilesCount = count($badProfiles);

        if ($badProfilesCount > 0) {
            $badProfilesCountText = "[B][COLOR=RED]".$badProfilesCount."[/COLOR][/B]";
            $text .= "I encountered " . $badProfilesCountText . " profiles who didn't submit their steam ID for checking. ";
        } else {
            $badProfilesCountText = "[B][COLOR=GREEN]".$badProfilesCount."[/COLOR][/B]";
            $text .= "I encountered " . $badProfilesCountText . " profiles without Steam IDs!";
        }

        $incorectIdCount = 0;
        foreach ($goodProfiles as $profile) {
            if ($profile['steam_url'] == "#") {
                $incorectIdCount = $incorectIdCount + 1;
            }
        }

        if ($incorectIdCount > 0) {
            $incorectIdCountText = "[B][COLOR=RED]".$incorectIdCount."[/COLOR][/B]";
            $text .= "I also found " . $incorectIdCountText . " cases of an incorect Steam ID format. ";
        } else {
            $incorectIdCountText = "[B][COLOR=GREEN]".$incorectIdCount."[/COLOR][/B]";
            $text .= "There were also " . $incorectIdCountText . " incorectly formatted steam IDs! ";
        }




        $suspectCount = 0;
        foreach ($goodProfiles as $profile) {
            if ($profile['steam_url'] != '#') {
                if ($profile['VAC_status'] == true || $profile['community_ban_status'] == true || $profile['econ_ban'] == "probation" || $profile["game_ban_num"] > 0) {
                    $suspectCount = $suspectCount + 1;
                }
            }
        }

        if ($suspectCount > 0) {
            $suspectCountText = "[B][COLOR=RED]".$suspectCount."[/COLOR][/B]";
            $text .= "In total, there are " . $suspectCountText . " suspect induviduals I thought you would be interested in. ";
        } else {
            $suspectCountText = "[B][COLOR=GREEN]".$suspectCount."[/COLOR][/B]";
            $text .= "There are " . $suspectCountText . " suspect induviduals! Congrats! ";
        }



        $threadURL = "https://dev.7cav.us/threads/";
        $text .= "Check report enclosed [URL=".$threadURL.$threadID."]" . "here[/URL]";

        $conversationDw = XenForo_DataWriter::create('XenForo_DataWriter_ConversationMaster');
        $conversationDw->set('user_id', $poster['user_id']);
        $conversationDw->set('username', $poster['username']);
        $conversationDw->set('title', $title);
        $conversationDw->set('open_invite', 1);
        $conversationDw->set('conversation_open', 1);

        $conversationDw->addRecipientUserIds($recipients);
        $messageDw = $conversationDw->getFirstMessageDw();
        $messageDw->set('message', $text);
        $conversationDw->preSave();
        $conversationDw->save();
        $conversation = $conversationDw->getMergedData();

        $convModel = XenForo_Model::create('XenForo_Model_Conversation');
        $convModel->markConversationAsRead(
            $conversation['conversation_id'], $poster['user_id'], XenForo_Application::$time
        );
    }
}
