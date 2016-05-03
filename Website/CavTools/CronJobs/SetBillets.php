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

    //Define Var

    //Renumber Array
    $members = array_values($members);

      foreach ($members as $member)
      // for($i=0; $i < $members[count('user_id')]; $i++)
        {

          //Get Secondary Positions
          $milpacs = $db->fetchRow('
        SELECT CAST(secondary_position_ids AS CHAR(100))
        FROM xf_pe_roster_user_relation
        WHERE user_id = 4
        AND secondary_position_ids IS NOT NULL
      ');

      $billetContent = " ";
      $list = $milpacs['secondary_group_ids'];
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

          $positonTitle = $getPositonTitle;
          $positonTitle = implode(',',$positonTitle);

              //Concatonate all user secondary billets
              $billetContent .= $positonTitle;
              $billetContent .= ", ";

              $userId = $member['user_id'];
              $userModel = XenForo_Model::create('XenForo_Model_User');
              $userProfile = $userModel->getFullUserById($userId);
              $customFields = unserialize($userProfile['custom_fields']);
              $customFields['Billets'] = $billetContent;

              $dw = XenForo_DataWriter::create('XenForo_DataWriter_User');
              $dw->setExistingData($userProfile);
              $dw->setCustomFields($customFields);
              $dw->save();
          }

      //     //Set XF user info
      //     $db->fetchAll('
      //   UPDATE xf_user_field_value
      //   SET field_value = '.$billetContent.'
      //   WHERE user_id = '.$member['user_id'].'
      //   AND field_id = "Billets"
      // ');

      //$db->update('xf_user_field_value', 'field_value' => .$db->quote($billetContent), 'user_id =' .$member['user_id'].);
      }
    }
  }
}
