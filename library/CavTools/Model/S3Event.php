<?php

class CavTools_Model_S3Event extends XenForo_Model {

    public function getEventList()
    {
        return $this->_getDb()->fetchAll('
			SELECT *
			FROM xf_ct_s3
		');
    }


}