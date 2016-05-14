<?php

class CavTools_CronJobs_XmlGenerator {
  public static function createXML() {

    //Get values from options
    $enable  = XenForo_Application::get('options')->enableXmlGenerator;

    if($enable) {

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
        $userIDs = $db->fetchAll("
        SELECT user_id
        FROM xf_user
        ORDER BY user_id ASC
        ");

        //Renumber Array
        $userIDs = array_values($userIDs);

        //Create a member section for each member
        foreach($userIDs as $user) {

          //Reset variables to false
          $officer = false;
          $nco = false;
          $enlisted = false;

          //Get user rank ID
          $usernameID = $db->fetchRow("
          SELECT rank_ID
          FROM xf_pe_roster_user_relation
          WHERE user_id = ".$user['user_id']."
          ");

          if ($usernameID['rank_ID'] != null) {

            if (in_array($usernameID['rank_ID'], $officerRanks)) {
              $officer = true;
            } else if (in_array($usernameID['rank_ID'], $ncoRanks)) {
              $nco = true;
            } else if (in_array($usernameID['rank_ID'], $enlistedRanks)) {
              $enlisted = true;
            } else


            //Start our prefix
            $nickPrefix = "";
            switch ($usernameID['rank_ID']) {
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

            //Get username
            $detailsUsername = $db->fetchRow("
            SELECT username
            FROM xf_user
            WHERE xf_user.user_id = ".$user['user_id']."
            ");

            //Get Real name
            $detailsRealname = $db->fetchRow("
            SELECT real_name
            FROM xf_pe_roster_user_relation
            WHERE user_id = ".$user['user_id']."
            ");

            //Get arma GUID
            $armaGUID = $db->fetchRow("
            SELECT field_value
            FROM xf_user_field_value
            WHERE xf_user_field_value.field_id='armaGUID'
            AND xf_user_field_value.user_id = ".$user['user_id']."
            ");

            //Get primary billet
            $primaryBillet = $db->fetchRow("
            SELECT xf_pe_roster_position.position_title
            FROM xf_pe_roster_position
            INNER JOIN xf_pe_roster_user_relation
            ON xf_pe_roster_position.position_id=xf_pe_roster_user_relation.position_id
            WHERE xf_pe_roster_user_relation.user_id = ".$user['user_id']."
            ");

            //Get secondary billets
            $secondaryBillets = $db->fetchRow("
            SELECT xf_user_field_value.field_value
            FROM xf_user_field_value
            WHERE field_id = 'Billets'
            AND user_id = ".$user['user_id']."
            ");

            //Form user variables from queries
            $nick = $nickPrefix;
            $nick .= $detailsUsername['username'];
            $GUID = "";
            if ($armaGUID['field_value'] != null) {
              $GUID   = $armaGUID['field_value'];
            }
            $name   = $nameTitle;
            $name  .= $detailsRealname['real_name'];
            $email  = $detailsUsername['username'];
            $email .= "@7cav.us";
            $remark = $primaryBillet['position_title'];
            if ($secondaryBillets['field_value'] != null) {
              $remark .= ", ";
              $remark .= $secondaryBillets['field_value'];
            }

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
      $xml->save("/var/www/html/xml/7Cav.xml");
    }
  }
}
