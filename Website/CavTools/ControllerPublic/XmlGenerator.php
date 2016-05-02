<?php

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
