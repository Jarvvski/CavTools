<?php

class CavTools_Model_Awol extends XenForo_Model {
    
    public function memberLastPost($userID)
    {
        $query = $this->_getDb()->fetchRow("
        		SELECT MAX(post_date)
				FROM xf_post
				WHERE user_id = '$userID' 
        ");
        
        return $query['MAX(post_date)'];
    }
    
    public function milpacsPosition($userID)
    {
        $query = $this->_getDb()->fetchRow("
                SELECT t1.position_id, t1.user_id, t2.position_title
                FROM xf_pe_roster_user_relation t1
                INNER JOIN xf_pe_roster_position t2
                ON t1.position_id = t2.position_id
                WHERE user_id = '$userID'
                ");
        return $query['position_title'];
    }
    
    public function getUsername($userID)
    {
        $query = $this->_getDb()->fetchRow("
                SELECT username
                FROM xf_user
                WHERE user_id = '$userID'
                ");
        return $query['username'];
    }
}