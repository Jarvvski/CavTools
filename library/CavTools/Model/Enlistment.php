<?php

class CavTools_Model_Enlistment extends XenForo_Model {

    public function getEnlistmentById($enlistmentID)
    {
        return $this->_getDb()->fetchRow("
            SELECT * 
            FROM xf_ct_rrd_enlistments
            WHERE enlistment_id = '$enlistmentID'
        ");
    }

    public function getAllEnlistment()
    {
        return $this->_getDb()->fetchAll('
        SELECT *
        FROM xf_ct_rrd_enlistments
        WHERE hidden = FALSE 
        ORDER BY enlistment_id ASC
        ');
    }

    public function getAllEnlistmentOrderByDate()
    {
        return $this->_getDb()->fetchAll('
        SELECT *
        FROM xf_ct_rrd_enlistments
        ORDER BY enlistment_date DESC
        ');
    }
    
    public function getAllHiddenEnlistment()
    {
        return $this->_getDb()->fetchAll('
        SELECT *
        FROM xf_ct_rrd_enlistments
        WHERE hidden = TRUE
        ORDER BY enlistment_id ASC
        ');
    }

    public function checkNameDupe($cavName)
    {
        return $this->_getDb()->fetchAll("
        SELECT username
        FROM xf_pe_roster_user_relation
        WHERE username LIKE '$cavName'
        ");
    }
    
    public function getLastRecord($relationID)
    {
        return $this->_getDb()->fetchRow("
        SELECT record_date, details
        FROM xf_pe_roster_service_record
        WHERE relation_id = '$relationID'
        ORDER BY record_id DESC LIMIT 1
        ");
    }

    public function getRelationID($userID)
    {
        return $this->_getDb()->fetchRow("
        SELECT relation_id, LAST_INSERT_ID()
        FROM xf_pe_roster_user_relation
        WHERE user_id = '$userID'
        ");
    }




}