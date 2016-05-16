<?php

class CavTools_ControllerPublic_GithubIssue extends XenForo_ControllerPublic_Abstract {
  public function actionIndex()
  {
    //Set Time Zone to UTC
    date_default_timezone_set("UTC");

    //Get DB
    $db = XenForo_Application::get('db');

    //View Parameters
    $viewParams = array(
    );

    //Send to template to display
    return $this->responseView('CavTools_ViewPublic_GithubIssue', 'CavTools_GithubIssue', $viewParams);
  }
}
