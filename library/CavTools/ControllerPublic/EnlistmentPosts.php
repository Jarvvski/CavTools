<?php

class CavTools_ControllerPublic_EnlistmentPosts extends XenForo_ControllerPublic_Abstract {

    public function actionIndex() {

        //Get values from options
        $enable = XenForo_Application::get('options')->enableAwolTracker;

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'awoltrackerView'))
        {
            throw $this->getNoPermissionResponseException();
        }

        //Set Time Zone to UTC
        date_default_timezone_set("UTC");

        //Get DB
        $db = XenForo_Application::get('db');

        //query
        $members = $db->fetchAll("
			SELECT user_id, username
			FROM xf_user
			ORDER BY username ASC
		");
    }
}