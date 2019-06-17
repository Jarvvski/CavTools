<?php

class CavTools_Model_RegiTrack extends XenForo_Model
{
    public function getSectionStats($position_group_id = null)
    {
        if (isset($position_group_id)) {
            $query = $this->_getDb()->fetchRow("
            SELECT count(relation_id)
            FROM xf_pe_roster_user_relation
            INNER JOIN xf_pe_roster_position
            ON xf_pe_roster_position.position_id = xf_pe_roster_user_relation.position_id
            WHERE xf_pe_roster_user_relation.roster_id = 1
            AND xf_pe_roster_position.position_group_id = '$position_group_id'
            ");
        } else {
            $query = $this->_getDb()->fetchRow("
            SELECT count(relation_id)
            FROM xf_pe_roster_user_relation
            WHERE roster_id = 1
            ");
        }
        return $query['count(relation_id)'];
    }

    public function getReserveStats($position_group_id = null)
    {
        if (isset($position_group_id)) {
            $query = $this->_getDb()->fetchRow("
            SELECT count(relation_id)
            FROM xf_pe_roster_user_relation
            INNER JOIN xf_pe_roster_position
            ON xf_pe_roster_position.position_id = xf_pe_roster_user_relation.position_id
            WHERE xf_pe_roster_user_relation.roster_id = 8
            AND xf_pe_roster_position.position_group_id = '$position_group_id'
            ");
        } else {
            $query = $this->_getDb()->fetchRow("
            SELECT count(relation_id)
            FROM xf_pe_roster_user_relation
            WHERE roster_id = 8
            ");
        }
        return $query['count(relation_id)'];
    }

    public function getPosGrpInfo($positionGroupID)
    {
        return $this->_getDb()->fetchRow("
        SELECT *
        FROM xf_pe_roster_position_group
        WHERE position_group_id = '$positionGroupID'
        ");
    }
}

