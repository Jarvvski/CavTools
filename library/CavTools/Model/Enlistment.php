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
        WHERE hidden = FALSE 
        ORDER BY enlistment_id DESC
        ', 'enlistment_id');
    }

    public function getAllEnlistmentOrderByDate()
    {
        return $this->fetchAllKeyed('
        SELECT *
        FROM xf_ct_rrd_enlistments
        ORDER BY enlistment_date DESC
        ', 'enlistment_date');
    }

    public function getEnlistmentByLastName()
    {
        return $this->_getDb()->fetchRow('
        SELECT *
        FROM xf_ct_rrd_enlistments
        WHERE last_name = ?
        ', $lastName);
    }
    
    public function getAllHiddenEnlistment()
    {
        return $this->fetchAllKeyed('
        SELECT *
        FROM xf_ct_rrd_enlistments
        WHERE hidden = TRUE
        ORDER BY enlistment_id ASC
        ', 'enlistment_id');
    }




}