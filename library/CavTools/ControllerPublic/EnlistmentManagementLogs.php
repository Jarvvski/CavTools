<?php

class CavTools_ControllerPublic_EnlistmentManagementLogs extends XenForo_ControllerPublic_Abstract
{
    public function actionIndex()
    {
        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'enlistmentManagementLogs'))
        {
            throw $this->getNoPermissionResponseException();
        }

        $logModel = $this->_getEnlistmentLogModel();
        $logs = $logModel->getAllLogs();
        $logRows = "";

        if (count($logs) != 0) {
            foreach ($logs as $log) {
                $logRows .= "<tr><td>". $log['log_id'] . "</td><td>" . $log['enlistment_id'] . "</td><td>" . $log['user_id'] . "</td><td>" . $log['username'] . "</td><td>" . date('m-d-y',$log['log_date']) . "</td><td>" . $log['action_taken'] ."</td></tr>" . PHP_EOL;
            }
        }

        //View Parameters
        $viewParams = array(
            'logRows' => $logRows,
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_EnlistmentManagementLogs', 'CavTools_EnlistmentManagementLogs', $viewParams);

    }

    protected function _getEnlistmentLogModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_EnlistmentLog' );
    }
}