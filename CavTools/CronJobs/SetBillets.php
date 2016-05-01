<?PHP

class CavTools_CronJobs_SetBillets
{
  public static function setBillets()
  {
    //Get values from options
    $enable = XenForo_Application::get('options')->enableSetBillets;

    if($enable) {

      //Get DB
      $db = XenForo_Application::get('db');

      //Get Milpacs ID
      $milpacsID = $db->fetchAll('
  SELECT user_id, secondary_position_ids
  FROM xf_pe_roster_user_relation
');

      //Concatonate all user secondary billets
      $billetContent = $milpacsID['secondary_group_ids'];
      $billetContent = implode(',', $billetContent);

      //Set XF user info
      $db->fetchAll('
    UPDATE xf_user_field_value
    SET field_value = '.$billetContent.'
    WHERE user_id = '.$milpacsID['user_id'].'
    AND field_id = Billets
  ');

    }
  }
}
