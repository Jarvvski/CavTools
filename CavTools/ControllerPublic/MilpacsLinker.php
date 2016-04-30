<?php

class CavTools_ControllerPublic_MilpacsLinker extends XenForo_ControllerPublic_Abstract
{
    public function actionIndex()
    {
      //Set Time Zone to UTC
      date_default_timezone_set('UTC');

      //Get DB
      $db = XenForo_Application::get('db');

      //query
      $members = $db->fetchAll('
    SELECT user_id, username
    FROM xf_user
    ORDER BY username ASC
  ');

    //Get values from options
    $enable = XenForo_Application::get('options')->linkerBoolean;

    //Get Milpacs ID
    $position = $db->fetchRow('
  SELECT t1.user_id
  FROM xf_pe_roster_user_relation t1
  WHERE user_id = '.$member['user_id'].'
  ');


    //Declare Variables
    $milpacsLink = '';



if ($enable) {
  $milpacsLink .= '<dl><dt><a href="http://http:/7Cav.us/Milpacs/rosters/profile?uniqueid='.$member['user_id'].'">Milpacs Profile</a></dt></dl>'
}

//View Parameters
$viewParams = array(
    'linkerBoolean' => $linkerBoolean,
    'milpacsLink' => $milpacsLink,
);


  //Send to template to display
  return $this->responseView('member_view', $viewParams);
}

public function fillMilpacs()
{
  if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId')) {
      throw $this->getNoPermissionResponseException();
  }

  //Set Time Zone to UTC
  date_default_timezone_set('UTC');

  //Get DB
  $db = XenForo_Application::get('db');

  //query
}
}
