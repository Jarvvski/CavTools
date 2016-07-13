<?php

class CavTools_ControllerPublic_XmlGenerator extends XenForo_ControllerPublic_Abstract {

    protected function _getXMLModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_XML' );
    }
    
    public function actionIndex() {

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'xmlGeneratorView'))
        {
            throw $this->getNoPermissionResponseException();
        }

        //Get values from options
        $enable  = XenForo_Application::get('options')->enableXmlGenerator;
        $model = $this->_getXMLModel();

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        $visitor = XenForo_Visitor::getInstance()->toArray();


        //Get values from options
        $rankGOA = XenForo_Application::get('options')->goaRankID;
        $rankGEN = XenForo_Application::get('options')->genRankID;
        $rankLTG = XenForo_Application::get('options')->ltgRankID;
        $rankMG  = XenForo_Application::get('options')->mgRankID;
        $rankBG  = XenForo_Application::get('options')->bgRankID;
        $rankCOL = XenForo_Application::get('options')->colRankID;
        $rankLTC = XenForo_Application::get('options')->ltcRankID;
        $rankMAJ = XenForo_Application::get('options')->majRankID;
        $rankCPT = XenForo_Application::get('options')->cptRankID;
        $rank1LT = XenForo_Application::get('options')->firstLtRankID;
        $rank2LT = XenForo_Application::get('options')->secondLtRankID;
        $rankCW5 = XenForo_Application::get('options')->WarrantFiveRankID;
        $rankCW4 = XenForo_Application::get('options')->WarrantFourRankID;
        $rankCW3 = XenForo_Application::get('options')->WarrantThreeRankID;
        $rankCW2 = XenForo_Application::get('options')->WarrantTwoRankID;
        $rankWO1 = XenForo_Application::get('options')->WarrantOneRankID;
        $rankWOC = XenForo_Application::get('options')->WarrantCandidateRankID;
        $rankCSM = XenForo_Application::get('options')->csmRankID;
        $rankSGM = XenForo_Application::get('options')->sgmRankID;
        $rank1SG = XenForo_Application::get('options')->firstSgtRankID;
        $rankMSG = XenForo_Application::get('options')->msgRankID;
        $rankSFC = XenForo_Application::get('options')->sfcRankID;
        $rankSSG = XenForo_Application::get('options')->ssgRankID;
        $rankSGT = XenForo_Application::get('options')->sgtRankID;
        $rankCPL = XenForo_Application::get('options')->cplRankID;
        $rankSPC = XenForo_Application::get('options')->spcRankID;
        $rankPFC = XenForo_Application::get('options')->pfcRankID;
        $rankPVT = XenForo_Application::get('options')->pvtRankID;
        $rankRCT = XenForo_Application::get('options')->rtcRankID;
        $dischPos = XenForo_Application::get('options')->dischargedPosition;
        $disDischPos = XenForo_Application::get('options')->dishonorableDischargePosition;

        $officerRanks	 = array($rankGOA, $rankGEN, $rankLTG,$rankMG, $rankBG, $rankCOL, $rankLTC, $rankMAJ, $rankCPT, $rank1LT, $rank2LT);
        $ncoRanks		   = array($rankCW5, $rankCW4, $rankCW3, $rankCW2, $rankWO1, $rankWOC, $rankCSM, $rankSGM, $rank1SG, $rankMSG, $rankSFC, $rankSSG, $rankSGT, $rankCPL);
        $enlistedRanks = array($rankSPC, $rankPFC, $rankPVT, $rankRCT);

        //Set Time Zone to UTC
        date_default_timezone_set("UTC");

        //Get DB
        $db = XenForo_Application::get('db');

        //BEGIN Create XML header
        $imp = new DOMImplementation;
        $docType = $imp->createDocumentType('squad', '', 'squad.dtd');
        $xml = $imp->createDocument("1.0", "", $docType);
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $xslt = $xml->createProcessingInstruction('xml-stylesheet', 'href="squad.xsl" type="text/xsl"');
        $xml->appendChild($xslt);
        //END XML header

        //BEGIN XML body creation
        $squad = $xml->createElement("squad");
        $squad->setAttribute("nick","7Cav");
        $xml->appendChild($squad);

        $squadNameNode = $xml->createElement("name", "7th Cavalry Regiment");
        $squad->appendChild($squadNameNode);
        $squadEmailNode = $xml->createElement("email", "Admin@7cav.us");
        $squad->appendChild($squadEmailNode);
        $squadWebsiteNode = $xml->createElement("web", "www.7cav.us");
        $squad->appendChild($squadWebsiteNode);
        $squadPictureNode = $xml->createElement("picture","7thCavCrest.paa");
        $squad->appendChild($squadPictureNode);
        $squadTitleNode = $xml->createElement("title", "7th Cavalry");
        $squad->appendChild($squadTitleNode);

        $userCounter = 0;

        for ($i=0;$i<3;$i++) {
            switch ($i) {
                case 0:
                    //BEGIN officers
                    $divider = $xml->createElement("member");
                    $divider->setAttribute("nick","");
                    $divider->setAttribute("id","");
                    $squad->appendChild($divider);
                    $dividerNameNode = $xml->createElement("name", "");
                    $divider->appendChild($dividerNameNode);
                    $dividerEmailNode = $xml->createElement("email", "-- Officers --");
                    $divider->appendChild($dividerEmailNode);
                    $dividerICQNode = $xml->createElement("icq", "");
                    $divider->appendChild($dividerICQNode);
                    $dividerRemarkNode = $xml->createElement("remark", "");
                    $divider->appendChild($dividerRemarkNode);
                    break;

                case 1:
                    //BEGIN NCOs
                    $divider = $xml->createElement("member");
                    $divider->setAttribute("nick","");
                    $divider->setAttribute("id","");
                    $squad->appendChild($divider);
                    $dividerNameNode = $xml->createElement("name", "");
                    $divider->appendChild($dividerNameNode);
                    $dividerEmailNode = $xml->createElement("email", "-- Non-commissioned officers --");
                    $divider->appendChild($dividerEmailNode);
                    $dividerICQNode = $xml->createElement("icq", "");
                    $divider->appendChild($dividerICQNode);
                    $dividerRemarkNode = $xml->createElement("remark", "");
                    $divider->appendChild($dividerRemarkNode);
                    break;

                case 2:
                    //BEGIN enlisted
                    $divider = $xml->createElement("member");
                    $divider->setAttribute("nick","");
                    $divider->setAttribute("id","");
                    $squad->appendChild($divider);
                    $dividerNameNode = $xml->createElement("name", "");
                    $divider->appendChild($dividerNameNode);
                    $dividerEmailNode = $xml->createElement("email", "-- Enlisted --");
                    $divider->appendChild($dividerEmailNode);
                    $dividerICQNode = $xml->createElement("icq", "");
                    $divider->appendChild($dividerICQNode);
                    $dividerRemarkNode = $xml->createElement("remark", "");
                    $divider->appendChild($dividerRemarkNode);
                    break;
            }

            //Basic user query
            $userIDs = $model->getMemberList();
            
            //Renumber Array
            $userIDs = array_values($userIDs);

            //Create a member section for each member
            foreach($userIDs as $user) {

                //Get primary billet
                $milpac = $model->getMilpac($user['user_id']);

                $discharged = false;
                if ($milpac['position_id'] == $disDischPos || $milpac['position_id'] == $dischPos) {
                    $discharged = true;
                }
                if (!$discharged) {

                    //Reset variables to false
                    $officer = false;
                    $nco = false;
                    $enlisted = false;


                    if ($milpac['rank_id'] != null) {

                        if (in_array($milpac['rank_id'], $officerRanks)) {
                            $officer = true;
                        } else if (in_array($milpac['rank_id'], $ncoRanks)) {
                            $nco = true;
                        } else if (in_array($milpac['rank_id'], $enlistedRanks)) {
                            $enlisted = true;
                        } else


                            //Start our prefix
                            $nickPrefix = "";
                            switch ($milpac['rank_id']) {
                                case $rankGOA: $nickPrefix = "=7Cav=GOA."; $nameTitle = "General of the Army "; break;
                                case $rankGEN: $nickPrefix = "=7Cav=GEN."; $nameTitle = "General ";break;
                                case $rankLTG: $nickPrefix = "=7Cav=LTG."; $nameTitle = "Lieutenant General ";break;
                                case $rankMG : $nickPrefix = "=7Cav=MG."; $nameTitle = "Major General "; break;
                                case $rankBG : $nickPrefix = "=7Cav=BG."; $nameTitle = "Brigadier General "; break;
                                case $rankCOL: $nickPrefix = "=7Cav=COL."; $nameTitle = "Colonel "; break;
                                case $rankLTC: $nickPrefix = "=7Cav=LTC."; $nameTitle = "Lieutenant Colonel "; break;
                                case $rankMAJ: $nickPrefix = "=7Cav=MAJ."; $nameTitle = "Major "; break;
                                case $rankCPT: $nickPrefix = "=7Cav=CPT."; $nameTitle = "Captain "; break;
                                case $rank1LT: $nickPrefix = "=7Cav=1LT."; $nameTitle = "First Lieutenant "; break;
                                case $rank2LT: $nickPrefix = "=7Cav=2LT."; $nameTitle = "Second Lieutenant "; break;
                                case $rankCW5: $nickPrefix = "=7Cav=CW5."; $nameTitle = "Chief Warrant Officer 5 "; break;
                                case $rankCW4: $nickPrefix = "=7Cav=CW4."; $nameTitle = "Chief Warrant Officer 4 "; break;
                                case $rankCW3: $nickPrefix = "=7Cav=CS3."; $nameTitle = "Chief Warrant Officer 3 "; break;
                                case $rankCW2: $nickPrefix = "=7Cav=CW2."; $nameTitle = "Chief Warrant Officer 2 "; break;
                                case $rankWO1: $nickPrefix = "=7Cav=WO1."; $nameTitle = "Warrant Officer 1 "; break;
                                case $rankWOC: $nickPrefix = "=7Cav=WOC" ; $nameTitle = "Warrant Officer Candidate "; break;
                                case $rankCSM: $nickPrefix = "=7Cav=CSM."; $nameTitle = "Command Sergeant Major "; break;
                                case $rankSGM: $nickPrefix = "=7Cav=SGM."; $nameTitle = "Sergeant Major "; break;
                                case $rank1SG: $nickPrefix = "=7Cav=1SG."; $nameTitle = "First Sergeant "; break;
                                case $rankMSG: $nickPrefix = "=7Cav=MSG."; $nameTitle = "Master Sergeant "; break;
                                case $rankSFC: $nickPrefix = "=7Cav=SFC."; $nameTitle = "Sergeant First Class "; break;
                                case $rankSSG: $nickPrefix = "=7Cav=SSG."; $nameTitle = "Staff Sergeant "; break;
                                case $rankSGT: $nickPrefix = "=7Cav=SGT."; $nameTitle = "Sergeant "; break;
                                case $rankCPL: $nickPrefix = "=7Cav=CPL."; $nameTitle = "Corporal "; break;
                                case $rankSPC: $nickPrefix = "=7Cav=SPC."; $nameTitle = "Specialist "; break;
                                case $rankPFC: $nickPrefix = "=7Cav=PFC."; $nameTitle = "Private First Class "; break;
                                case $rankPVT: $nickPrefix = "=7Cav=PVT."; $nameTitle = "Private "; break;
                                case $rankRCT: $nickPrefix = "=7Cav=RCT."; $nameTitle = "Recruit "; break;
                            default:       $nickPrefix = "Failed::";   break;
                        }


                        //Get arma GUID
                        $armaGUID = $model->getGUID($user['user_id']);

                        //Form user variables from queries
                        $nick = $nickPrefix;
                        $nick .= $milpac['username'];
                        $GUID = "";
                        if ($armaGUID['field_value'] != null) {
                            $GUID   = $armaGUID['field_value'];
                            $userCounter++;
                        }
                        $name   = $nameTitle;
                        $name  .= $milpac['real_name'];
                        $email  = $milpac['username'];
                        $email .= "@7cav.us";
                        $remark = $milpac['position_title'];

                        //Generate our members
                        //If rank type is officer
                        if ($officer && ($i == 0)) {
                            // create officers
                            $member = $xml->createElement("member");
                            $member->setAttribute("id",$GUID);
                            $member->setAttribute("nick",$nick);
                            $squad->appendChild($member);
                            $memberNameNode = $xml->createElement("name", $name);
                            $member->appendChild($memberNameNode);
                            $memberEmailNode = $xml->createElement("email", $email);
                            $member->appendChild($memberEmailNode);
                            $memberICQNode = $xml->createElement("icq", null);
                            $member->appendChild($memberICQNode);
                            $memberRemarkNode = $xml->createElement("remark", $remark);
                            $member->appendChild($memberRemarkNode);
                        }
                        //If rank type is NCO
                        if ($nco && ($i == 1)) {
                            // create NCOs
                            $member = $xml->createElement("member");
                            $member->setAttribute("id",$GUID);
                            $member->setAttribute("nick",$nick);
                            $squad->appendChild($member);
                            $memberNameNode = $xml->createElement("name", $name);
                            $member->appendChild($memberNameNode);
                            $memberEmailNode = $xml->createElement("email", $email);
                            $member->appendChild($memberEmailNode);
                            $memberICQNode = $xml->createElement("icq", null);
                            $member->appendChild($memberICQNode);
                            $memberRemarkNode = $xml->createElement("remark", $remark);
                            $member->appendChild($memberRemarkNode);
                        }
                        //If rank type is enlisted
                        if ($enlisted && ($i == 2)) {
                            // create enlisted
                            $member = $xml->createElement("member");
                            $member->setAttribute("id",$GUID);
                            $member->setAttribute("nick",$nick);
                            $squad->appendChild($member);
                            $memberNameNode = $xml->createElement("name", $name);
                            $member->appendChild($memberNameNode);
                            $memberEmailNode = $xml->createElement("email", $email);
                            $member->appendChild($memberEmailNode);
                            $memberICQNode = $xml->createElement("icq", null);
                            $member->appendChild($memberICQNode);
                            $memberRemarkNode = $xml->createElement("remark", $remark);
                            $member->appendChild($memberRemarkNode);
                        }
                    }
                }
            }
        }

        $redirect = XenForo_Application::get('options')->redirect;
        $xml->save($redirect);
        

        //View Parameters
        $viewParams = array(
            'userCounter' => $userCounter,
            'xml' => $xml->saveXML()
        );
        
        $this->tweet($visitor);

        //Send to template for displaying
        return $this->responseView('CavTools_ViewPublic_XmlGenerator', 'CavTools_XmlGenerator', $viewParams);
    }

    public function tweet($visitor)
    {
        $twitterModel = $this->_getTwitterBot();
        $text = $visitor['username'] . " just rebuilt the XML. Did it crash ArmA?";
        $hashtag = "#ArmA3 #7Cav #IMO";
        $twitterModel->postStatus($text, $hashtag);
    }

    protected function _getTwitterBot()
    {
        return $this->getModelFromCache( 'CavTools_Model_IMOBot' );
    }
}
