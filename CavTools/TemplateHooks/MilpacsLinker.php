<?php

class CavTools_TemplateHooks_MilpacsLinker
{
  public static function infoContent($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
  {
    if ($hookName == 'member_view_info_block')
		{

      //Get DB
      $db = XenForo_Application::get('db');

      //Get XF member ID
      $member = $db->fetchRow('
    SELECT user_id, username
    FROM xf_user
  ');

      //Get values from options
      $linker = XenForo_Application::get('options')->linkerBoolean;

      //Declare Variables
      $milpacsProfile = '';
      $templateContent = '';
      $milpacsUrl = '/rosters/profile?uniqueid=';

      //Get Milpacs ID
      $milpacsID = $db->fetchRow('
  SELECT t1.relation_id, t1.user_id
  FROM xf_pe_roster_user_relation t1
  WHERE user_id = '.$member['user_id'].'
');

      $milpacsProfile = $milpacsUrl.$milpacsID['relation_id'];
      $templateContent = '<dl><dt>Milpacs:</dt><dd><a href='.$milpacsProfile.' class="OverlayTrigger">Some Guy</a></dd></dl>';

      if($linker) {
      $contents .= $templateContent;
      }
		}
  }
}
