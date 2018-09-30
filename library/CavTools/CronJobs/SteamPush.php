<?php

class CavTools_CronJobs_SteamPush {

    public static function pushSteamIds() {

        $enlistmentModel = XenForo_Model::create('CavTools_Model_Enlistment');
        $userModel = XenForo_Model::create('XenForo_Model_User');

        $enlistments = $enlistmentModel->getMostRecentEnlistmentPerUser();

        foreach ($enlistments as $enlistment) {

            $userProfile = $userModel->getFullUserById($enlistment['user_id']);
            $customFields = unserialize($userProfile['custom_fields']);

            if (array_key_exists('armaGUID',$customFields)) {
                if ($customFields['armaGUID'] == '') {

                    $customFields['armaGUID'] = $enlistment['steamID'];

                    // Use datawriter
                    $dw = XenForo_DataWriter::create('XenForo_DataWriter_User');
                    $dw->setOption(XenForo_DataWriter_User::OPTION_ADMIN_EDIT, true);
                    $dw->setExistingData($userProfile);
                    $dw->setCustomFields($customFields);
                    $dw->save();
                }
            }
        }
    }
}
