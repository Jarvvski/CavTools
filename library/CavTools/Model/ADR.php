<?php

class CavTools_Model_ADR extends XenForo_Model {

    public function getLatestADR()
    {
        return $this->_getDb()->fetchRow("
                    SELECT text FROM xf_ct_adr
                    ORDER BY adr_id DESC
                    LIMIT 1;
                    ");
    }

    public function getPositionData($positionID)
    {
        return $this->_getDb()->fetchRow("
                    SELECT position_id, position_title, materialized_order, position_group_id
                    FROM xf_pe_roster_position 
                    WHERE position_id = '$positionID'
                    ");
    }

    public function getTextById($id)
    {
        return $this->_getDb()->fetchRow("
                    SELECT *
                    FROM xf_ct_adr
                    WHERE adr_id ='$id'
                    ");
    }

    public function getPrimaryPosIDs()
    {
        // TODO - change unicorn values for Xen options

        return $this->_getDb()->fetchAll("
                    SELECT t1.position_id, t1.position_title, t4.title, t3.username, t3.user_id, t1.materialized_order, t1.position_group_id
                    FROM xf_pe_roster_position t1
                    INNER JOIN xf_pe_roster_position_group t5
                    ON t5.position_group_id = t1.position_group_id
                    INNER JOIN xf_pe_roster_user_relation t2
                    ON t2.position_id = t1.position_id
                    INNER JOIN xf_user t3
                    ON t3.username = t2.username
                    INNER JOIN xf_pe_roster_rank t4
                    on t2.rank_id = t4.rank_id
                    WHERE t2.roster_id = 1
                    ORDER BY t1.materialized_order ASC, t5.display_order ASC
                    ");
    }
    
    public function getSecondaryPosIDs()
    {
        return $this->_getDb()->fetchAll("
                    SELECT t1.user_id, t2.title, t1.username, CAST(t1.secondary_position_ids AS CHAR(100))
                    FROM xf_pe_roster_user_relation t1
                    INNER JOIN xf_pe_roster_rank t2
                    ON t2.rank_id = t1.rank_id
                    WHERE CAST(secondary_position_ids AS CHAR(100)) != ''
                    ");
    }

    public function getRegHQIDs()
    {
        return XenForo_Application::get('options')->regiHqIds;
    }

    public function getSuptDeptIDs()
    {
        return XenForo_Application::get('options')->suptDeptIds;
    }



    public function getBatt1Ids()
    {
        return XenForo_Application::get('options')->batt1Ids;
    }

    public function getBatt2Ids()
    {
        return XenForo_Application::get('options')->batt2Ids;
    }
}