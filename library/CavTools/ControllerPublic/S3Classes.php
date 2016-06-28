<?php

class CavTools_ControllerPublic_S3Classes extends XenForo_ControllerPublic_Abstract
{
    public function actionIndex()
    {

        //Get values from options
        $enable = XenForo_Application::get('options')->enableS3Classes;

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 's3ClassView'))
        {
            throw $this->getNoPermissionResponseException();
        }

        if (XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'canActionClass'))
        {
            $canAction = true;
        } else {
            $canAction = false;
        }

        //Set Time Zone to UTC
        date_default_timezone_set("UTC");

        //Get DB
        $db = XenForo_Application::get('db');

        $model = $this->_getS3ClassModel();
        $classes = $model->getClassList();
        $memberURL = '/members/';
        $classList = "";

        if(count($classes) != 0) {
            foreach ($classes as $class) {
                
                $member = $memberURL . $class['user_id'];
                $poster = $class['username'];

                if ($canAction) {
                    $classList .= "<tr><td>". $class['class_id'] ."</td><td>" . $class['class_name'] . "</td><td><a href=" . $member . "><b>" . $poster . "</b></a></td><td><input type=\"checkbox\" name=\"classes[]\" value=" . $class['class_id'] . "></td></tr>" . PHP_EOL;
                } else {
                    $classList .= "<tr><td>". $class['class_id'] . "</td><td>" . $class['class_name'] . "</td><td><a href=" . $member . "><b>" . $poster . "</b></a></td></tr>" . PHP_EOL;
                }
            }
        }

        //View Parameters
        $viewParams = array(
            'canAction' => $canAction,
            'classList' => $classList,
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_s3classes', 'CavTools_S3Classes', $viewParams);
    }

    public function actionPost()
    {
        //Action can only be called via post
        $this->_assertPostOnly();

        $classes = $_POST['classes'];

        foreach ($classes as $class) {
            $this->setHidden($class);
        }

        // redirect after post
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('s3classes'),
            new XenForo_Phrase('class_updated')
        );
    }

    public function setHidden($class)
    {
        $dw = XenForo_DataWriter::create('CavTools_DataWriter_S3Classes');
        $dw->setExistingData($class);
        $dw->set('hidden', 1);
        $dw->save();
    }


    protected function _getS3ClassModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_S3Event' );
    }

}