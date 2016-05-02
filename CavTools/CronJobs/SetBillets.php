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

      //query
      $members = $db->fetchAll('
    SELECT user_id
    FROM xf_user
    ORDER BY user_id ASC
  ');

    //Define Vars
    $billetContent = "";
    $positionIDs = "";

    //Renumber Array
    $members = array_values($members);

      foreach ($members as $member)
      // for($i=0; $i < $members[count('user_id')]; $i++)
        {

          //Get Secondary Positions
          $milpacs = $db->fetchRow('
        SELECT CAST(secondary_position_ids AS CHAR(100))
        FROM xf_pe_roster_user_relation
        WHERE user_id = '.$member['user_id'].'
        AND secondary_position_ids IS NOT NULL
      ');

      $list = $milpacs;
      $list = str_replace(',','',$list);
      $list = explode(',',$list);

      foreach ($list as $ID)
      {

              //Get Position Titles
              $positonTitle = $db->fetchRow('
            SELECT position_title
            FROM xf_pe_roster_position
            WHERE position_id = '.$ID.'
          ');

              //Concatonate all user secondary billets
              $billetContent .= $positonTitle['position_title'] + ', ';

          }

          //Set XF user info
          $db->fetchAll('
        UPDATE xf_user_field_value
        SET field_value = '.$billetContent.'
        WHERE user_id = '.$member['user_id'].'
        AND field_id = "Billets"
      ');

      }
    }
  }
}
