<?php

class CavTools_Model_S3Event extends XenForo_Model {

    public function getEventList()
    {
        return $this->_getDb()->fetchAll('
			SELECT *
			FROM xf_ct_s3
		');
    }

    public function canViewEvents()
    {
        $this->standardizeViewingUserReference($viewingUser);

        return XenForo_Permission::hasPermission($viewingUser['permissions'], 'peRosters', 'manage');
    }
}