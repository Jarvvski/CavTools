<?php

class CavTools_ControllerPublic_AutoADR extends XenForo_ControllerPublic_Abstract {

    // Runs on Page load
    public function actionIndex()
    {
        // Get enable value
        $enable = XenForo_Application::get('options')->enableADR;

        // If not enabled, escape
        if(!$enable)
        {
            throw $this->getNoPermissionResponseException();
        }

        // If cannot view, escape
        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'viewADR'))
        {
            throw $this->getNoPermissionResponseException();
        }

        // Get the latest ADR construct
        $data = $this->createList();

        // View Parameters
        $viewParams = array(
            'data' => $data,
        );

        // Send to template to display
        return $this->responseView('CavTools_ViewPublic_ADR', 'CavTools_adr', $viewParams);
    }

    // Runs on Page POST
    public function actionPost()
    {
        // Create the new ADR then store in DB
        $data = $this->createList();
        $this->createData($data);

        // redirect after post
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('adr'),
            new XenForo_Phrase('ADR_rebuilding')
        );
    }

    // Func to build ADR
    public function createList()
    {
        // Set initial vars
        $data = "";
        $newLine= "<br />";

        // Get ADR data model
        $model = $this->_getADRModel();

        // Get home URL from enlistment options
        $home = XenForo_Application::get('options')->homeURL;

        // Begin members build
        $members = $model->getPrimaryPosIDs();

        // Get all secondary billets
        $secondaryBillets = $model->getSecondaryPosIDs();

        // Begin build of secondary billets
        $extraMembers = array();
        foreach ($secondaryBillets as $secondaryBillet)
        {
            // All the individual position IDs
            $billets = explode(',', $secondaryBillet['CAST(t1.secondary_position_ids AS CHAR(100))']);

            // Iterate through all Pos IDs
            foreach ($billets as $billet) {
                // Get the data for given Pos ID
                $positionData = $model->getPositionData($billet);

                // Build a member for that position
                $secondaryMembers = array(
                    'position_id' => $positionData['position_id'],
                    'position_title' => $positionData['position_title'],
                    'title' => $secondaryBillet['title'],
                    'username' => $secondaryBillet['username'],
                    'user_id' => $secondaryBillet['user_id'],
                    'materialized_order' => $positionData['materialized_order'],
                    'position_group_id' => $positionData['position_group_id']
                );

                // Add this member to secondary billet list
                array_push($extraMembers ,$secondaryMembers);
            }
        }
        // Combine secondary billets with primary
        $members = array_merge($members, $extraMembers);
        // Sort member list via Milpacs display order
        $this->sksort($members,"materialized_order", true);

        // Begin headings
        $hqData = "<br><h3>Regimental Headquarters</h3><hr><br>";
        $bat1HQData = "<br><h4>1-7 Command</h4><hr><br>";
        $suptData = "<br><h3>Support Departments</h3><hr><br>";
        $alpha1Data = "<br><h4>Alpha Company 1-7</h4><hr><br>";
        $bravo1Data = "<br><h4>Bravo Company 1-7</h4><hr><br>";
        $charlie1Data = "<br><h4>Charlie Company 1-7</h4><hr><br>";
        $delta1Data = "<br><h4>Delta Company 1-7 (ACV)</h4><hr><br>";
        $echo1Data = "<br><h4>Echo Company 1-7</h4><hr><br>";
        $training1Data = "<br><h4>Training Unit 1-7</h4><hr><br>";
        $bat2HQData = "<br><h4>2-7 Command</h4><hr><br>";
        $bravo2Data = "<br><h4>Bravo Company 2-7</h4><hr><br>";
        $alpha2Data = "<br><h4>Alpha Company 2-7</h4><hr><br>";
        $charlie2Data = "<br><h4>Charlie Company 2-7</h4><hr><br>";
        $newRecruitsData = "<br><h4>New Recruits</h4><hr><br>";
        $starterData = "<br><h4>Starter Company</h4><hr><br>";

        // Begin Support headings
        $jagData = "<br><h4>Judge Advocate General's Office (JAG)</h4><hr><br>";
        $s1Data = "<br><h4>S1 Department</h4><hr><br>";
        $s2Data = "<br><h4>S2 Military Intelligence Department</h4><hr><br>";
        $mpData = "<br><h4>Military Police</h4><hr><br>";
        $s3Data = "<br><h4>S3 Department</h4><hr><br>";
        $s5Data = "<br><h4>S5 Public Relations Department</h4><hr><br>";
        $rrdData = "<br><h4>Regimental Recruiting Department (RRD)</h4><hr><br>";
        $rtcData = "<br><h4>Recruit Training Command (RTC)</h4><hr><br>";
        $ncoData = "<br><h4>NCO Academy</h4><hr><br>";
        $s6Data = "<br><h4>S6 - Regimental IMO (Information Management Office)</h4><hr><br>";
        $cagData = "<br><h4>CAG (Combat Applications Group)</h4><hr><br>";

        // Begin 1BN headings
        $alpha11PLData = "<br><h5>1st Platoon: (Rotary Transport)</h5><hr><br>";
        $alpha21PLData = "<br><h5>2nd Platoon: (Rotary & Fixed Attack)</h5><hr><br>";
        $bravo11PLData = "<br><h5>1st Platoon: (Armor)</h5><hr><br>";
        $bravo21PLData = "<br><h5>2nd Platoon: (Mechanized Infantry)</h5><hr><br>";
        $bravo31PLData = "<br><h5>3rd Platoon: (Mechanized Infantry)</h5><hr><br>";
        $charlie11PLData = "<br><h5>1st Platoon: (Airborne Infantry)</h5><hr><br>";
        $charlie21PLData = "<br><h5>2nd Platoon: (Airborne Infantry)</h5><hr><br>";
        $charlie31PLData = "<br><h5>3rd Platoon: (Airborne Weapons Infantry)</h5><hr><br>";
        $delta11PLData = "<br><h5>1st Platoon: (Infantry)</h5><hr><br>";
        $delta21PLData = "<br><h5>2nd Platoon: (Support)</h5><hr><br>";
        $echo11PLData   = "<br><h5>1st Platoon: </h5><hr><br>";
        $echo21PLData   = "<br><h5>2nd Platoon: </h5><hr><br>";
        $echo31PLData  = "<br><h5>3rd Platoon: </h5><hr><br>";
        $echo41PLData  = "<br><h5>4th Platoon: </h5><hr><br>";
        $trainingUnitData = "<br><h5>In Training</h5><hr><br>";

        // Begin 2BN headings
        $bravo12PLData = "<br><h5>1st Platoon: (Squad)</h5><hr><br>";
        $bravo22PLData = "<br><h5>2nd Platoon: (Squad)</h5><hr><br>";
        $bravo32PLData = "<br><h5>3rd Platoon: (Squad)</h5><hr><br>";
        $bravo52PLData = "<br><h5>5th Platoon: (Insurgency)</h5><hr><br>";
        $charlie12PLData = "<br><h5>1st Platoon: (CS:GO)</h5><hr><br>";

        // Main pos IDs
        $posGrpIDs = XenForo_Application::get('options')->posGrpIDs;
        $posGrpIDs = explode(',',$posGrpIDs);

        // 1BN
        $alpha11PLIDs = explode(',',XenForo_Application::get('options')->alpha11PLID);
        $alpha21PLIDs = explode(',',XenForo_Application::get('options')->alpha21PLID);
        $bravo11PLIDs = explode(',',XenForo_Application::get('options')->bravo11PLID);
        $bravo21PLIDs = explode(',',XenForo_Application::get('options')->bravo21PLID);
        $bravo31PLIDs = explode(',',XenForo_Application::get('options')->bravo31PLID);
        $charlie11PLIDs = explode(',',XenForo_Application::get('options')->charlie11PLID);
        $charlie21PLIDs = explode(',',XenForo_Application::get('options')->charlie21PLID);
        $charlie31PLIDs = explode(',',XenForo_Application::get('options')->charlie31PLID);
        $delta11PLIDs = explode(',',XenForo_Application::get('options')->delta11PLID);
        $delta21PLIDs = explode(',',XenForo_Application::get('options')->delta21PLID);
        $echo11PLIDs = explode(',', XenForo_Application::get('options')->echo11PLID);
        $echo21PLIDs = explode(',', XenForo_Application::get('options')->echo21PLID);
        $echo31PLIDs = explode(',', XenForo_Application::get('options')->echo31PLID);
        $echo41PLIDs = explode(',', XenForo_Application::get('options')->echo41PLID);
        $trainingUnitIDs = explode(',',XenForo_Application::get('options')->trainingUnitID);

        // 2BN
        $bravo12PLIDs = explode(',',XenForo_Application::get('options')->bravo12PLID);
        $bravo22PLIDs = explode(',',XenForo_Application::get('options')->bravo22PLID);
        $bravo32PLIDs = explode(',',XenForo_Application::get('options')->bravo32PLID);
        $bravo52PLIDs = explode(',',XenForo_Application::get('options')->bravo52PLID);
        $charlie12PLIDs = explode(',',XenForo_Application::get('options')->charlie12PLID);

        // Support department IDs
        $s1PosID = explode(',',XenForo_Application::get('options')->s1PosID);
        $s2PosID = explode(',',XenForo_Application::get('options')->s2PosID);
        $s3PosID = explode(',',XenForo_Application::get('options')->s3PosID);
        $s5PosID = explode(',',XenForo_Application::get('options')->s5PosID);
        $s6PosID = explode(',',XenForo_Application::get('options')->s6PosID);
        $rtcPosID = explode(',',XenForo_Application::get('options')->rtcPosID);
        $rrdPosID = explode(',',XenForo_Application::get('options')->rrdPosID);
        $jagPosID = explode(',',XenForo_Application::get('options')->jagPosID);
        $mpPosID = explode(',',XenForo_Application::get('options')->mpPosID);
        $ncoPosID = explode(',',XenForo_Application::get('options')->ncoPosID);
        $cagPosID = explode(',',XenForo_Application::get('options')->cagPosID);

        // Squad leader POS IDs
        $slPosID = explode(',',XenForo_Application::get('options')->slPosID);

        // Create data entry for each member
        foreach ($members as $member) {
            // Build Profile link
            $userURL = '<a href="/members/'.$member['user_id'].'">'.$member['title']." ".$member['username'].'</a>';

            // Decide which data group to assign
            switch ($member['position_group_id'])
            {
                case $posGrpIDs[0]:
                    $hqData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    break;
                case $posGrpIDs[1]:
                    $bat1HQData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    break;
                case $posGrpIDs[2]:
                    // All support department
                    // Decide on which department via position ID
                    switch($member['position_id'])
                    {
                        case (in_array($member['position_id'], $s1PosID)):
                            $s1Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                            break;
                        case (in_array($member['position_id'], $s2PosID)):
                            $s2Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                            break;
                        case (in_array($member['position_id'], $s3PosID)):
                            $s3Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                            break;
                        case (in_array($member['position_id'], $s5PosID)):
                            $s5Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                            break;
                        case (in_array($member['position_id'], $s6PosID)):
                            $s6Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                            break;
                        case (in_array($member['position_id'], $rtcPosID)):
                            $rtcData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                            break;
                        case (in_array($member['position_id'], $rrdPosID)):
                            $rrdData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                            break;
                        case (in_array($member['position_id'], $jagPosID)):
                            $jagData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                            break;
                        case (in_array($member['position_id'], $mpPosID)):
                            $mpData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                            break;
                        case (in_array($member['position_id'], $ncoPosID)):
                            $ncoData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                            break;
                        case (in_array($member['position_id'], $cagPosID)):
                            $cagData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                            break;
                    }
                    break;
                case $posGrpIDs[3]:
                    // All Alpha company
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $alpha11PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $alpha11PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $alpha11PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        case (in_array($member['position_id'], $alpha21PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $alpha21PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $alpha21PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        default: $alpha1Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[4]:
                    // All Bravo company
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $bravo11PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $bravo11PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $bravo11PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        case (in_array($member['position_id'], $bravo21PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $bravo21PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $bravo21PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        case (in_array($member['position_id'], $bravo31PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $bravo31PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $bravo31PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        default: $bravo1Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[5]:
                    // All Charlie company
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $charlie11PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $charlie11PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $charlie11PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        case (in_array($member['position_id'], $charlie21PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $charlie21PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $charlie21PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        case (in_array($member['position_id'], $charlie31PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $charlie31PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $charlie31PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        default: $charlie1Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[6]:
                    // All Delta company
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $delta11PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $delta11PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $delta11PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        case (in_array($member['position_id'], $delta21PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $delta21PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $delta21PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        default: $delta1Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[7]:
                    // All Echo company
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $echo11PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $echo11PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $echo11PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        case (in_array($member['position_id'], $echo21PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $echo21PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $echo21PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        case (in_array($member['position_id'], $echo31PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $echo31PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $echo31PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        case (in_array($member['position_id'], $echo41PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $echo41PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $echo41PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        default: $echo1Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[8]:
                    // All Training Unit
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $trainingUnitIDs)):
                            $trainingUnitData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        default: $training1Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[9]:
                    $bat2HQData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    break;
                case $posGrpIDs[10]:
                    $alpha2Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    break;
                case $posGrpIDs[11]:
                    // All Bravo company
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $bravo12PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $bravo12PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $bravo12PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        case (in_array($member['position_id'], $bravo22PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $bravo22PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $bravo22PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        case (in_array($member['position_id'], $bravo32PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $bravo32PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $bravo32PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        case (in_array($member['position_id'], $bravo52PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $bravo52PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $bravo52PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        default: $bravo2Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[12]:
                    // All Charlie company
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $charlie12PLIDs)):
                            if (strpos($member['position_title'], 'Squad Leader' ) !== false || strpos($member['position_title'], 'Section Leader' ) !== false) {
                                $charlie12PLData .= "<br><p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            } else {
                                $charlie12PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            }
                            break;
                        default: $charlie2Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[13]:
                    $newRecruitsData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    break;
                case $posGrpIDs[14]:
                    $starterData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    break;
            }
        }

        // Build Company Data
        $alpha1Data .= $alpha11PLData . $alpha21PLData;
        $bravo1Data .= $bravo11PLData . $bravo21PLData . $bravo31PLData;
        $charlie1Data .= $charlie11PLData . $charlie21PLData . $charlie31PLData;
        $delta1Data .= $delta11PLData . $delta21PLData;
        $echo1Data .= $echo11PLData . $echo21PLData . $echo31PLData . $echo41PLData;
        $training1Data .= $trainingUnitData;
        $bravo2Data .= $bravo12PLData . $bravo22PLData . $bravo32PLData . $bravo52PLData;
        $charlie2Data .= $charlie12PLData;


        // Build Battalion Data
        $bat1Total = "<h3>1st Battalion</h3><hr><br>" . $bat1HQData . $alpha1Data . $bravo1Data . $charlie1Data . $delta1Data . $echo1Data . $training1Data. $starterData;
        $bat2Total = "<h3>2nd Battalion</h3><hr><br>" . $bat2HQData . $alpha2Data . $bravo2Data . $charlie2Data;
        $recruitTotal = $newRecruitsData;

        // Build Support Data
        $suptData  .=  $s1Data . $newLine . $s2Data . $newLine . $s3Data . $newLine .
            $s5Data . $newLine . $s6Data .$newLine . $mpData . $newLine . $jagData .
            $newLine .$rrdData . $newLine .$rtcData . $newLine .$ncoData . $newLine .
            $cagData. $newLine;

        // Combine data sets
        $data .= $newLine . $hqData . $suptData . $bat1Total .$newLine . $newLine. $bat2Total . $newLine . $recruitTotal;

        // Return new ADR
        return $data;
    }

    // Sorting fun
    public function sksort(&$array, $subkey="id", $sort_ascending=false) {

        if (count($array))
            $temp_array[key($array)] = array_shift($array);

        foreach($array as $key => $val){
            $offset = 0;
            $found = false;
            foreach($temp_array as $tmp_key => $tmp_val)
            {
                if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
                {
                    $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                        array($key => $val),
                        array_slice($temp_array,$offset)
                    );
                    $found = true;
                }
                $offset++;
            }
            if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
        }

        if ($sort_ascending) $array = array_reverse($temp_array);

        else $array = $temp_array;
    }

    // Gets ADR data model
    protected function _getADRModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_ADR' );
    }
}
