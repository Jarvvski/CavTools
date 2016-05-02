<?php

class CavTools_TemplateHooks_MemberCardMilpacs
{
  public static function infoContent($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
  {
    if ($hookName == 'member_card_stats')
		{

      //Get values from options
      $linker = XenForo_Application::get('options')->linkerBoolean;


      if($linker) {

        //Get DB
        $db = XenForo_Application::get('db');

        //Get user paramater
        $user = $hookParams['user'];

        //Get XF user info
        $member = $db->fetchrow('
      SELECT user_id, username
      FROM xf_user
      WHERE user_id = '.$user.'
    ');



        //Declare Variables
        $milpacsProfile = '';
        $templateContent = '';
        $milpacsUrl = '/rosters/profile?uniqueid=';
        $username = 'user.name';

        //Get Milpacs ID
        $milpacsID = $db->fetchRow('
    SELECT relation_id, user_id
    FROM xf_pe_roster_user_relation
    WHERE user_id = '.$member['user_id'].'
  ');

        if($milpacsID['relation_id'] != null) {
          $milpacsProfile = $milpacsUrl.$milpacsID['relation_id'];
          $templateContent = '<dt>Milpacs:</dt> <dd><a class="Treck" href='.$milpacsProfile.'>'.$member['username'].'</a></dd>';
          $contents .= $templateContent;
        }
      }
		}
  }
}
