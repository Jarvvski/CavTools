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

        // If cannot action, don't display the button to rebuild
        if (XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'canUpdateADR'))
        {
            $canAction = true;
        } else {
            $canAction = false;
        }

        // Get the latest ADR construct
        $model = $this->_getADRModel();
        $data = $model->getLatestADR();


        // View Parameters
        $viewParams = array(
            'data' => $data['text'],
            'canAction' => $canAction
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
        $alpha11PLData = "<br><h5>1st Platoon (Rotary Transport)</h5><hr><br>";
        $alpha21PLData = "<br><h5>2nd Platoon (Rotary & Fixed Attack)</h5><hr><br>";
        $bravo11PLData = "<br><h5>1st Platoon (Armor)</h5><hr><br>";
        $bravo21PLData = "<br><h5>2nd Platoon (Mechanized Infantry)</h5><hr><br>";
        $bravo31PLData = "<br><h5>3rd Platoon (Mechanized Infantry)</h5><hr><br>";
        $charlie11PLData = "<br><h5>1st Platoon (Airborne Infantry)</h5><hr><br>";
        $charlie21PLData = "<br><h5>2nd Platoon (Airborne Infantry)</h5><hr><br>";
        $charlie31PLData = "<br><h5>3rd Platoon (Combat Engineers)</h5><hr><br>";
        $charlie41PLData = "<br><h5>4th Platoon (ACV)</h5><hr><br>";
        $trainingUnitData = "<br><h5>In Training</h5><hr><br>";

        // Begin 2BN headings
        $bravo12PLData = "<br><h5>1st Platoon: (Squad)</h5><hr><br>";
        $bravo22PLData = "<br><h5>2nd Platoon: (Squad)</h5><hr><br>";
        $bravo32PLData = "<br><h5>3rd Platoon: (Squad)</h5><hr><br>";
        $bravo52PLData = "<br><h5>5th Platoon (Insurgency)</h5><hr><br>";
        $charlie12PLData = "<br><h5>1st Platoon (CS:GO)</h5><hr><br>";

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
        $charlie41PLIDs = explode(',',XenForo_Application::get('options')->charlie41PLID);
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
                            $alpha11PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        case (in_array($member['position_id'], $alpha21PLIDs)):
                            $alpha21PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        default: $alpha1Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[4]:
                    // All Bravo company
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $bravo11PLIDs)):
                            $bravo11PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        case (in_array($member['position_id'], $bravo21PLIDs)):
                            $bravo21PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        case (in_array($member['position_id'], $bravo31PLIDs)):
                            $bravo31PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        default: $bravo1Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[5]:
                    // All Charlie company
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $charlie11PLIDs)):
                            $charlie11PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        case (in_array($member['position_id'], $charlie21PLIDs)):
                            $charlie21PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        case (in_array($member['position_id'], $charlie31PLIDs)):
                            $charlie31PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        case (in_array($member['position_id'], $charlie41PLIDs)):
                            $charlie41PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        default: $charlie1Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[6]:
                    // All Training Unit
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $trainingUnitIDs)):
                            $trainingUnitData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        default: $training1Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[7]:
                    $bat2HQData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    break;
                case $posGrpIDs[8]:
                    $alpha2Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    break;
                case $posGrpIDs[9]:
                    // All Bravo company
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $bravo12PLIDs)):
                            $bravo12PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        case (in_array($member['position_id'], $bravo22PLIDs)):
                            $bravo22PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        case (in_array($member['position_id'], $bravo32PLIDs)):
                            $bravo32PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        case (in_array($member['position_id'], $bravo52PLIDs)):
                            $bravo52PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        default: $bravo2Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[10]:
                    // All Charlie company
                    // Decide on which platoon via position ID
                    switch($member['position_id']) {
                        case (in_array($member['position_id'], $charlie12PLIDs)):
                            $charlie12PLData .= "<p>" . $member['position_title'] . " : " . $userURL . $newLine . "</p>";
                            break;
                        default: $charlie2Data .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    }
                    break;
                case $posGrpIDs[11]:
                    $newRecruitsData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    break;
                case $posGrpIDs[12]:
                    $starterData .= "<p>".$member['position_title'] . " : " . $userURL . $newLine."</p>";
                    break;
            }
        }
        
        // Build Company Data
        $alpha1Data .= $alpha11PLData . $alpha21PLData;
        $bravo1Data .= $bravo11PLData . $bravo21PLData . $bravo31PLData;
        $charlie1Data .= $charlie11PLData . $charlie21PLData . $charlie31PLData . $charlie41PLData;
        $training1Data .= $trainingUnitData;
        $bravo2Data .= $bravo12PLData . $bravo22PLData . $bravo32PLData . $bravo52PLData;
        $charlie2Data .= $charlie12PLData;
        

        // Build Battalion Data
        $bat1Total = "<h3>1st Battalion</h3><hr><br>" . $bat1HQData . $alpha1Data . $bravo1Data . $charlie1Data . $training1Data. $starterData;
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

    // Calls DataWriter
    public function createData($data)
    {
        $dw = XenForo_DataWriter::create('CavTools_DataWriter_ADR');
        $dw->set('text', $data);
        $dw->save();
    }

    // Gets ADR data model
    protected function _getADRModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_ADR' );
    }
}

