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
    if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'XmlGeneratorView'))
    {
      throw $this->getNoPermissionResponseException();
    }

    //Set Time Zone to UTC
    date_default_timezone_set("UTC");

    //Get DB
    $db = XenForo_Application::get('db');

    //Member query
    $memberIDs = $db->fetchAll("
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

    foreach($memberIDs as $memberID) {

      $member = $squad->addChild("member id='' nick='' ");
      $member->addChild("name");
      $member->addChild("email");
      $member->addChild("icq");
      $member->addChild("remark");

    }

    //View Parameters
    $viewParams = array(
      'xmlOutput' => $xmlOutput
    );

    //Send to template for displaying
    return $this->responseView('CavTools_ViewPublic_XmlGenerator', 'CavTools_XmlGenerator', $viewParams);
  }
}
