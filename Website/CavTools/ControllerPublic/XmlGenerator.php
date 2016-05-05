<?php

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

    //Basic member query
    $memberInfo = $db->fetchAll("
      SELECT user_id, username
      FROM xf_user
      ORDER BY username ASC
    ");

    //Set Variables
    $xmlOutput = "";

    //Create XML header
    $xml = new SimpleXMLElement('<?xml version="1.0"?><!DOCTYPE squad SYSTEM "squad.dtd">
    <?xml-stylesheet href="squad.xsl" type="text/xsl"?>');

    //Begin XML body creation
    $squad = $xml->addChild("squad nick='' ");
    $squad->addChild("name");
    $squad->addChild("email");
    $squad->addChild("web");
    $squad->addChild("picture");
    $squad->addChild("title");

    //Renumber Array
    $memberIDs = array_values($memberIDs);

    //Create a member section for each member
    foreach($memberInfo as $member) {

      //Member custom field query
      $memberFields = $db->fetchAll('
        SELECT field_value, field_id
        FROM xf_user_field_value
        WHERE user_id = '.$member['user_id'].'
        ');

      //Generate member xml section
      //TODO
      // - use DB values for items
      // - maybe extrapolate nick based on username and cav rank
      // - check if field has value, if not set value to null

      $member = $squad->addChild("member id='' nick='' ");
      $member->addChild("name", "".);
      $member->addChild("email", "" .$);
      $member->addChild("icq", "");
      $member->addChild("remark", "");

    }

    //TODO 
    // - check if need to output dtd, xsl aswell
    // - set file paths as well as template output for manual checking
    // - create cron job to automatically run when not using for checking xml
    // - replace S1 XML: IMO bot on ADR ;)

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
