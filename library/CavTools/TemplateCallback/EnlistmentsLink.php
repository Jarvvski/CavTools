<?php

class CavTools_TemplateCallback_EnlistmentsLink {

    public static function getEnlistments($content, $params, XenForo_Template_Abstract $template) {

        $visitor = XenForo_Visitor::getInstance()->toArray();

        $url = "/enlistments/view?id=" . $params['varNeeded'];

        //Get DB
        $db = XenForo_Application::get('db');

        $enlistments =  $db->fetchRow("
            SELECT *
            FROM xf_ct_rrd_enlistments
            WHERE user_id = ?
        ", $params['varNeeded']);

        $viewParams = array(
            'URL' => $url,
            'enlistments' => $enlistments
        );
        return $template->create('CavTools_enlistmentsLink', $viewParams);
    }

}
