<?php

//TODO
// - use DB values for items
// - maybe extrapolate nick based on username and cav rank
// - check if field has value, if not set value to null
// - check if need to output dtd, xsl aswell
// - set file paths as well as template output for manual checking
// - create cron job to automatically run when not using for checking xml
// - replace S1 XML: IMO bot on ADR ;)

class CavTools_CronJobs_XmlGenerator {
  public function createXML() {

    //Get values from options
    $enable  = XenForo_Application::get('options')->enableXmlGenerator;
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
    $ncoRanks		   = array($rankCW5, $rankCW4, $rankCW3, $rankCW2, $rankWO1, $rankCSM, $rankSGM, $rank1SG, $rankMSG, $rankSFC, $rankSSG, $rankSGT, $rankCPL);
    $enlistedRanks = array($rankSPC, $rankPFC, $rankPVT, $rankRCT);

    //If not enabled in ACP, throw permission error for all users
    if(!$enable) {
      throw $this->getNoPermissionResponseException();
    }

    //If user does not have perms, throw permission eror
    if (!XenForo_Visitor::getInstance()->hasPermission('XmlGeneratorView'))
    {
      throw $this->getNoPermissionResponseException();
    }

    //Set Time Zone to UTC
    date_default_timezone_set("UTC");

    //Get DB
    $db = XenForo_Application::get('db');

    //Basic user query
    $userIDs = $db->fetchAll("
    SELET user_id
    FROM xf_users
    ORDER BY user_id ASC
    ");

    //Set Variables
    $xmlOutput  = "";

    //BEGIN Create XML header
    $xml = new SimpleXMLElement('<?xml version="1.0"?><!DOCTYPE squad SYSTEM "squad.dtd">
    <?xml-stylesheet href="squad.xsl" type="text/xsl"?>');
    //END XML header

    //BEGIN XML body creation
    $squad = $xml->addChild("squad nick='7Cav' ");
    $squad->addChild("name", "7th Cavalry Regiment");
    $squad->addChild("email", "Admin@7cav.us");
    $squad->addChild("web", "www.7cav.us");
    $squad->addChild("picture","7thCavCrest.paa");
    $squad->addChild("title", "7th Cavalry");

    for ($i=0;$i<3;$i++) {
      switch ($i) {
        case 0:
        //BEGIN officers
        $divider = $squad->addChild("member id='' nick='' ");
        $divider->addChild("name", "".);
        $divider->addChild("email", "-- Officers --");
        $divider->addChild("icq", "");
        $divider->addChild("remark", "");
        break;

        case 1:
        //BEGIN NCOs
        $divider = $squad->addChild("member id='' nick='' ");
        $divider->addChild("name", "".);
        $divider->addChild("email", "-- Non-commissioned officers --");
        $divider->addChild("icq", "");
        $divider->addChild("remark", "");
        break;

        case 2:
        //BEGIN enlisted
        $divider = $squad->addChild("member id='' nick='' ");
        $divider->addChild("name", "".);
        $divider->addChild("email", "-- Enlisted --");
        $divider->addChild("icq", "");
        $divider->addChild("remark", "");
        break;
      }

      //Renumber Array
      $userIDs = array_values($userIDs);

      //Create a member section for each member
      foreach($userIDs as $user) {

        //Reset variables to false
        $officer = false;
        $nco = false;
        $enlisted = false;

        //Get username groups
        $usernameGroups = $db->fetchAll("
        SELECT xf_user.username ,xf_user_group_relation.user_group_id
        FROM xf_user
        INNER JOIN xf_user_group_relation
        ON xf_user.user_id=xf_user_group_relation.user_id
        WHERE xf_user.user_id = ".$user['user_id']."
        AND xf_user_group_relation.user_id = ".$user['user_id']."
        ORDER BY xf_user.username ASC
        ");

        //Loop to get rank type
        foreach ($usernameGroups['xf_user_group_relation.user_group_id'] as $key => $value)
        {
          switch ($key) {
            case (in_array($key, $officerRanks, true)):
                $officer = true;
            break;

            case (in_array($key, $ncoRanks, true)):
                $nco = true;
            break;

            case (in_array($key, $enlistedRanks, true)):
                $enlisted = true;
            break;
            }
          //Get user nick prefix
          $nick = getNickPrefix($key);
          }
        }

        //Get user details
        $details = $db->fetchRow("
        SELECT xf_user.username, xf_pe_roster_user_relation.real_name, xf_user_field_value.field_value
        FROM xf_user
        INNER JOIN xf_pe_roster_user_relation
        ON xf_user.user_id=xf_pe_roster_user_relation.user_id
        INNER JOIN xf_user_field_value
        ON xf_pe_roster_user_relation.user_id=xf_user_field_value.user_id
        WHERE xf_user.user_id = ".$user['user_id']."
        AND xf_pe_roster_user_relation.user_id = ".$user['user_id']."
        AND xf_user_field_value.field_id = 'armaGUID'
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
        $nick  .= $details['xf_user.username'];
        $GUID   = $details['xf_user_field_value.field_value'];
        $name   = $details['xf_pe_roster_user_relation.real_name'];
        $email  = $details['xf_user.username'] + "@7cav.us";
        $remark = $primaryBillet['xf_pe_roster_position.position_title'] + ", " + $secondaryBillets['xf_user_field_value.field_value'];

        //Generate our members
        switch (true) {
          //If rank type is officer
          case ($officer && ($i == 0))
          // create officers
          $member = $squad->addChild("member id=".$GUID." nick=".$nick." ");
          $member->addChild("name", $name);
          $member->addChild("email", $email);
          $member->addChild("icq", "");
          $member->addChild("remark", $remark);
          break;
          //If rank type is NCO
          case ($nco && ($i == 1))
          // create NCOs
          $member = $squad->addChild("member id=".$GUID." nick=".$nick." ");
          $member->addChild("name", $name);
          $member->addChild("email", $email);
          $member->addChild("icq", "");
          $member->addChild("remark", $remark);
          break;
          //If rank type is enlisted
          case($enlisted && ($i == 2))
          // create enlisted
          $member = $squad->addChild("member id=".$GUID." nick=".$nick." ");
          $member->addChild("name", $name);
          $member->addChild("email", $email);
          $member->addChild("icq", "");
          $member->addChild("remark", $remark);
          break;
        }
      }
    }

    //Set the xml created as the output for our template
    $xmlOutput = print($xml);

    $xmlFile = fopen("/var/www/html/7CavXML/7Cav.xml","w");
    fwrite($xmlFile, $xmlOutput);
    fclose($xmlFile);
  }

  //Get our nick prefix
  public function getNickPrefix($key) {

    //Reset our prefix
    $nickPrefix = "";

    //Use key from positions
    switch($key) {
      //If value is in this array
      case (array_intersect($key, $officerRanks)):
        switch (array_intersect($key, $officerRanks)) {
          case $rankGOA: $nickPrefix = "=7Cav=GOA." break;
          case $rankGEN: $nickPrefix = "=7Cav=GEN." break;
          case $rankLTG: $nickPrefix = "=7Cav=LTG." break;
          case $rankMG : $nickPrefix = "=7Cav=MG."  break;
          case $rankBG : $nickPrefix = "=7Cav=BG."  break;
          case $rankCOL: $nickPrefix = "=7Cav=COL." break;
          case $rankLTC: $nickPrefix = "=7Cav=LTC." break;
          case $rankMAJ: $nickPrefix = "=7Cav=MAJ." break;
          case $rankCPT: $nickPrefix = "=7Cav=CPT." break;
          case $rank1LT: $nickPrefix = "=7Cav=1LT." break;
          case $rank2LT: $nickPrefix = "=7Cav=2LT." break;
          default      : $nickPrefix = "Failed::"   break;
        }
        break;
      //If value is in this array
      case (array_intersect($key, $ncoRanks)):
        switch (array_intersect($key, $ncoRanks)) {
          case $rankCW5: $nickPrefix = "=7Cav=CW5." break;
          case $rankCW4: $nickPrefix = "=7Cav=CW4." break;
          case $rankCW3: $nickPrefix = "=7Cav=CS3." break;
          case $rankCW2: $nickPrefix = "=7Cav=CW2." break;
          case $rankWO1: $nickPrefix = "=7Cav=WO1." break;
          case $rankCSM: $nickPrefix = "=7Cav=CSM." break;
          case $rankSGM: $nickPrefix = "=7Cav=SGM." break;
          case $rank1SG: $nickPrefix = "=7Cav=1SG." break;
          case $rankMSG: $nickPrefix = "=7Cav=MSG." break;
          case $rankSFC: $nickPrefix = "=7Cav=SFC." break;
          case $rankSSG: $nickPrefix = "=7Cav=SSG." break;
          case $rankSGT: $nickPrefix = "=7Cav=SGT." break;
          case $rankCPl: $nickPrefix = "=7Cav=CPL." break;
          default      : $nickPrefix = "Failed::"   break:
        }
        break;
      //If value is in this array
      case (array_intersect($key, $enlistedRanks)):
        switch (array_intersect($key, $enlistedRanks)) {
          case $rankSPC: $nickPrefix = "=7Cav=SPC." break;
          case $rankPFC: $nickPrefix = "=7Cav=PFC." break;
          case $rankPVT: $nickPrefix = "=7Cav=PVT." break;
          case $rankRCT: $nickPrefix = "=7Cav=RCT." break;
          default      : $nickPrefix = "Failed::"   break;
        }
        break;
      }
    //Send our prefix back
    return $nickPrefix;
  }
}
