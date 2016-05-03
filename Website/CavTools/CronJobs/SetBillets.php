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

    //Renumber Array
    $members = array_values($members);

      foreach ($members as $member)
        {


          //Get Secondary Positions
          $milpacs = $db->fetchRow('
        SELECT CAST(secondary_position_ids AS CHAR(100))
        FROM xf_pe_roster_user_relation
        WHERE user_id = '.$member['user_id'].'
        AND secondary_position_ids IS NOT NULL
      ');

      $billetContent = " ";

      //If user has no milpacs, dont run
      if($milpacs == !null) {
      $list = $milpacs;
      $list = implode(',',$list);
      $list = explode(',',$list);

      foreach ($list as $ID)
      {
              //Get Position Titles
              $getPositonTitle = $db->fetchRow('
            SELECT position_title
            FROM xf_pe_roster_position
            WHERE position_id = '.$ID.'
          ');

          //Deal with positions
          $positonTitle = $getPositonTitle;
          $positonTitle = implode(',',$positonTitle);

              //Concatonate all user secondary billets
              $billetContent .= $positonTitle;
              $billetContent .= ",";
          }

          //Define datawriter variables
          $userId = $member['user_id'];
          $userModel = XenForo_Model::create('XenForo_Model_User');
          $userProfile = $userModel->getFullUserById($userId);
          $customFields = unserialize($userProfile['custom_fields']);
          $customFields['Billets'] = rtrim($billetContent, ",");

          //Use datawriter
          $dw = XenForo_DataWriter::create('XenForo_DataWriter_User');
          $dw->setExistingData($userProfile);
          $dw->setCustomFields($customFields);
          $dw->save();
        }
      }
    }
  }
}
