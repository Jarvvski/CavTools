<?php

//TODO
// - use DB values for items
// - maybe extrapolate nick based on username and cav rank
// - check if field has value, if not set value to null
// - check if need to output dtd, xsl aswell
// - set file paths as well as template output for manual checking
// - create cron job to automatically run when not using for checking xml
// - replace S1 XML: IMO bot on ADR ;)

class CavTools_ControllerPublic_XmlGenerator extends XenForo_ControllerPublic_Abstract {
  public function actionIndex() {

    //Get values from options
    $enable = XenForo_Application::get('options')->enableXmlGenerator;

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

    //Real name + rank milpacs query
    $rankInfo = $db->fetchAll("
      SELECT xf_pe_roster_user_relation.real_name, xf_pe_roster_rank.title
      FROM xf_pe_roster_user_relation
      INNER JOIN xf_pe_roster_rank
      ON xf_pe_roster_user_relation.rank_id=xf_pe_roster_rank.rank_id
      ORDER BY xf_pe_roster_rank.title ASC
    ");

    // SELECT Customers.CustomerName, Orders.OrderID
    // FROM Customers
    // INNER JOIN Orders
    // ON Customers.CustomerID=Orders.CustomerID
    // ORDER BY Customers.CustomerName;

    //Set Variables
    $xmlOutput = "";
    $officerID = 11;
    $ncoID = 12;
    $enlistedID = 13;

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

        $officer = false;
        $nco = false;
        $enlisted = false;

        $usernameGroups = $db->fetchAll("
        SELECT xf_user.username ,xf_user_group_relation.user_group_id
        FROM xf_user
        INNER JOIN xf_user_group_relation
        ON xf_user.user_id=xf_user_group_relation.user_id
        WHERE xf_user.user_id = ".$user['user_id']."
        AND xf_user_group_relation.user_id = ".$user['user_id']."
        ORDER BY xf_user.username ASC
        ");

        //Would be better to define which positons mean officer, NCO or enlisted
        foreach ($usernameGroups['xf_user_group_relation.user_group_id'] as $key => $value)
        {
          switch ($key) {
            case 11:
                $officer = true;
            break;

            case 12:
                $nco = true;
            break;

            case 13:
                $enlisted = true;
            break;
          }
        }

        switch (true) {
          case ($officer && ($i == 0))
          // create officers
          break;

          case ($nco && ($i == 1))
          // create NCOs
          break;

          case($enlisted && ($i == 2))
          // create enlisted
          break;
        }
      }
    }

      // Testing
      // if (in_array($officerID, $usernameGroups['xf_user_group_relation.user_group_id'], true))

    //Set the xml created as the output for our template
    $xmlOutput = $xml;

    //View Parameters
    $viewParams = array(
      'xmlOutput' => $xmlOutput
    );

    //Send to template for displaying
    return $this->responseView('CavTools_ViewPublic_XmlGenerator', 'CavTools_XmlGenerator', $viewParams);
  }
}
