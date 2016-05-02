<?php
// Title: CavTools XML Generator 0.1
// Desc:  Should generate XML sheet for 7Cav purposes in Arma 3
// Date:  02MAY2016


//TODO
// - Options
// - Group perms
// - Route Prefixes on website
// - Template


class CavTools_ControllerPublic_XmlGenerator extends XenForo_ControllerPublic_Abstract {

  public function actionIndex() {

    if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'XmlGeneratorView')) {
        throw $this->getNoPermissionResponseException();
      }

      //Set Time Zone to UTC
      date_default_timezone_set('UTC');

      //Get DB
      $db = XenForo_Application::get('db');

      $xml = new SimpleXMLElement('<xml/>');
  }
}
