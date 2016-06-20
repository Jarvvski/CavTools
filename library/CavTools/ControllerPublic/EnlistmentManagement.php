<?php

class CavTools_ControllerPublic_EnlistmentManagement extends XenForo_ControllerPublic_Abstract {

    public function actionIndex() {

        //Get values from options
        $enable = XenForo_Application::get('options')->enableEnlistmentManagement;

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'EnlistmentManagement'))
        {
            throw $this->getNoPermissionResponseException();
        }

        if (XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'canSubmitEnlistment'))
        {
            $canAction = true;
        } else {
            $canAction = false;
        }

        //Set Time Zone to UTC
        date_default_timezone_set("UTC");

        //Get DB
        $db = XenForo_Application::get('db');

        $normalEnlistments = " ";
        $reEnlistments = " ";
        $threadURL = '/threads/';

        $enlistModel = $this->_getEnlistmentModel();
        $enlistments = $enlistModel->getAllEnlistment();

        if (count($enlistments) != 0) {
            foreach ($enlistments as $enlistment) {
                
                $thread = $threadURL . $enlistment['thread_id'];
                $banned = $enlistment['vac_ban'];
                $underage = $enlistment['under_age'];
                $reenlistment = $enlistment['reenlistment'];
                $daysSince = round((time() - $enlistment['enlistment_date']) / 86400);

                if ($banned) {
                    $banStatus = '<td style="color:red;">';
                } else {
                    $banStatus = '<td>';
                }

                if ($underage) {
                    $ageStatus = '<td style="color:red;">';
                } else {
                    $ageStatus = '<td>';
                }
                if ($canAction) {
                    if (!$reenlistment) {
                        if (count($enlistments) != 0) {
                            $normalEnlistments .= "<tr><td><a href=" . $thread . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . date('m-d-y', $enlistment['enlistment_date']) . "</td><td>" . $daysSince . "</td><td>" . $enlistment['first_name'] . "</td><td>" . $enlistment['last_name'] . "</td><td>" . $enlistment['recruiter'] . "</td>$banStatus " . $enlistment['steamID'] . "</td>$ageStatus" . $enlistment['age'] . "</td><td><input type=\"checkbox\" name=\"enlistments[]\" value=" . $enlistment['enlistment_id'] . "></td></tr>" . PHP_EOL;
                        }
                    }

                    if ($reenlistment) {
                        if (count($enlistments) != 0) {
                            $reEnlistments .= "<tr><td><a href=" . $thread . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . date('m-d-y', $enlistment['enlistment_date']) . "</td><td>" . $daysSince . "</td><td>" . $enlistment['first_name'] . "</td><td>" . $enlistment['last_name'] . "</td><td>" . $enlistment['recruiter'] . "</td>$banStatus" . $enlistment['steamID'] . "</td>$ageStatus" . $enlistment['age'] . "</td><td><input type=\"checkbox\" name=\"enlistments[]\" value=" . $enlistment['enlistment_id'] . "></td></tr>" . PHP_EOL;
                        }
                    }
                } else {
                    if (!$reenlistment) {
                        if (count($enlistments) != 0) {
                            $normalEnlistments .= "<tr><td><a href=" . $thread . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . date('m-d-y', $enlistment['enlistment_date']) . "</td>" . $daysSince . "<td></td><td>" . $enlistment['first_name'] . "</td><td>" . $enlistment['last_name'] . "</td><td>" . $enlistment['recruiter'] . "</td>$banStatus " . $enlistment['steamID'] . "</td>$ageStatus" . $enlistment['age'] . "</td></tr>" . PHP_EOL;
                        }
                    }

                    if ($reenlistment) {
                        if (count($enlistments) != 0) {
                            $reEnlistments .= "<tr><td><a href=" . $thread . "><b>" . $enlistment['enlistment_id'] . "</b></a></td><td>" . date('m-d-y', $enlistment['enlistment_date']) . "</td><td>" . $daysSince . "</td><td>" . $enlistment['first_name'] . "</td><td>" . $enlistment['last_name'] . "</td><td>" . $enlistment['recruiter'] . "</td>$banStatus" . $enlistment['steamID'] . "</td>$ageStatus" . $enlistment['age'] . "</td></tr>" . PHP_EOL;
                        }
                    }
                }
            }
        }

        //View Parameters
        $viewParams = array(
            'normalEnlistments' => $normalEnlistments,
            'reEnlistments' => $reEnlistments,
            'canAction' => $canAction,
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_EnlistmentManagement', 'CavTools_EnlistmentManagement', $viewParams);
    }

    public function actionPost()
    {
        // TODO - Send replies for options
    }

    protected function _getEnlistmentModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_Enlistment' );
    }
}