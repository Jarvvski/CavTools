<?php

class CavTools_TemplateCallback_MilpacNavLink {

    public static function getMilpac($userID) {

    }

    public static function getMilpacID($content, $params, XenForo_Template_Abstract $template) {
         // Get whatever your var is here however you need to.

        $db = XenForo_Application::get('db');

        $visitor = XenForo_Visitor::getInstance()->toArray();

        $milpac =  $db->fetchRow("
            SELECT *
            FROM xf_pe_roster_user_relation
            WHERE user_id = ?
        ", $visitor['user_id']);

        $url = "/rosters/profile?uniqueid=" . $milpac['relation_id'];

        $viewParams = array(
            'milpac' => $milpac,
            'URL' => $url
        );
        return $template->create('CavTools_navLink', $viewParams);
    }

}
