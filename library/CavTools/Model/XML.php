<?php

class CavTools_Model_XML extends XenForo_Model {

    public function getMemberList()
    {
        return $this->_getDb()->fetchAll("
            SELECT user_id
            FROM xf_user
            ORDER BY user_id ASC
        ");
    }

    public function getMilpac($userID)
    {
        return $this->_getDb()->fetchRow("
            SELECT *
            FROM xf_pe_roster_user_relation
            INNER JOIN xf_pe_roster_position
            on xf_pe_roster_position.position_id = xf_pe_roster_user_relation.position_id
            WHERE xf_pe_roster_user_relation.user_id = '$userID'
        ");
    }
    
    public function getGUID($userID)
    {
        return $this->_getDb()->fetchRow("
				  SELECT field_value
				  FROM xf_user_field_value
				  WHERE xf_user_field_value.field_id='armaGUID'
				  AND xf_user_field_value.user_id = '$userID'
        ");
    }

}