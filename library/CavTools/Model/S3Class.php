<?php

class CavTools_Model_S3Class extends XenForo_Model {

    public function getClassByClassName($className)
    {
        return $this->_getDb()->fetchRow("
        SELECT *
        FROM xf_ct_s3_classes
        WHERE class_name = '$className'
        ");
    }
}