<?php

class CavTools_Model_S3Event extends XenForo_Model {

    public function getEventById($id)
    {
        return $this->_getDb()-fetchRow("
        SELECT *
        FROM xf_ct_s3_events
        WHERE event_id = '$id'
        ");
    }

    public function getEventList()
    {
        return $this->_getDb()->fetchAll('
		SELECT *
		FROM xf_ct_s3_events
		WHERE hidden = false
		');
    }
}