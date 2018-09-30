<?php

class CavTools_Model_RegiTrack extends XenForo_Model {

    public function getSteamProfiles() {
        return $this->_getDb()->fetchRow("
            SELECT t1.user_id, t1.field_value, t2.username, t2.relation_id, t3.title
            FROM xf_user_field_value t1
            INNER JOIN xf_pe_roster_user_relation t2
            ON t1.user_id = t2.user_id
            INNER JOIN xf_pe_roster_rank t3
            ON t2.rank_id = t3.rank_id
            WHERE field_id = 'armaGUID'
        ");
    }
}
