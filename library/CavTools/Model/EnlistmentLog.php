<?php

class CavTools_Model_EnlistmentLog extends XenForo_Model {

    public function getLogById($logID)
    {
        return $this->_getDb()->fetchRow("
            SELECT * 
            FROM xf_ct_rrd_logs
            WHERE log_id = '$logID'
        ");
    }
    
    public function getAllLogs()
    {
        return $this->_getDb()->fetchAll('
        SELECT *
        FROM xf_ct_rrd_logs
        ORDER BY log_id ASC
        ');
    }
    
    public function getLogsByUserID($userID)
    {
        return $this->_getDb()->fetchAll("
        SELECT *
        FROM xf_ct_rrd_logs
        WHERE user_id = '$userID'
        ORDER BY log_id ASC
        ");
    }
}