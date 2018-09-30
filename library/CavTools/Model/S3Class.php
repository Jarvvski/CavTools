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

    public function getClassList()
    {
        return $this->_getDb()->fetchAll("
        SELECT *
        FROM xf_ct_s3_classes
        WHERE hidden = FALSE
        ");
    }

    public function getClassById($id)
    {
        return $this->_getDb()->fetchRow("
        SELECT *
        FROM xf_ct_s3_classes
        WHERE class_id = '$id'
        ");
    }
}