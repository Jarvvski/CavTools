<?php

class CavTools_Model_Enlistment extends XenForo_Model {

    public function getEnlistmentById($enlistmentID)
    {
        return $this->_getDb()->fetchRow('
            SELECT *
            FROM xf_ct_rrd_enlistments
            WHERE enlistment_id = ?
        ', $enlistmentID);
    }

    public function getAllEnlistment()
    {
        return $this->fetchAllKeyed('
        SELECT *
        FROM xf_ct_rrd_enlistments
        ORDER BY enlistment_id DESC
        ', 'enlistment_id');
    }

}