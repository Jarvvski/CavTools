<?php

class CavTools_ControllerPublic_S3ClassCreate extends XenForo_ControllerPublic_Abstract
{
    public function actionIndex()
    {
        //Get values from options
        $enable = XenForo_Application::get('options')->enableS3ClassCreate;

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'S3ClassCreate'))
        {
            throw $this->getNoPermissionResponseException();
        }

        // Games from options
        $games = XenForo_Application::get('options')->s3Games;
        $games = explode(',', $games);

        //Set Time Zone to UTC
        date_default_timezone_set("UTC");

        //Get DB
        $db = XenForo_Application::get('db');

        //View Parameters
        $viewParams = array(
            'defaultMessage' => "",
            'games' => $games
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_ClassEdit', 'CavTools_S3ClassCreation', $viewParams);
    }

    public function actionPost()
    {

        // Action can only be called via post
        $this->_assertPostOnly();

        // Get poster info
        $visitor = XenForo_Visitor::getInstance()->toArray();

        // Get form values
        $className = $this->_input->filterSingle('className', XenForo_Input::STRING);
        $classText = $this->getHelper('Editor')->getMessageText('message', $this->_input);

        $className = htmlspecialchars($className);
        $classText = htmlspecialchars($classText);

        $this->createData($className, $classText, $visitor);

        // redirect after post
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('s3classes'),
            new XenForo_Phrase('class_created')
        );
    }

    public function createData($className, $classText, $visitor)
    {
        $dw = XenForo_DataWriter::create('CavTools_DataWriter_S3Class');
        $dw->set('class_name', $className);
        $dw->set('class_text', $classText);
        $dw->set('username', $visitor['username']);
        $dw->set('user_id', $visitor['user_id']);
        $dw->save();
    }
}
