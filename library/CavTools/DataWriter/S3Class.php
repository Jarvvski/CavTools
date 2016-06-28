<?php

class CavTools_DataWriter_S3Class extends XenForo_DataWriter {

    /**
     * Gets the fields that are defined for the table. See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'xf_ct_s3_classes' => array(
                'class_id' => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
                'class_name' => array('type' => self::TYPE_STRING),
                'class_text' => array('type' => self::TYPE_STRING),
                'username' => array('type' => self::TYPE_STRING),
                'user_id' => array('type' => self::TYPE_INT),
                'hidden' => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
            )
        );
    }

    /**
     * Gets the actual existing data out of data that was passed in. See parent for explanation.
     *
     * @param mixed
     *
     * @see XenForo_DataWriter::_getExistingData()
     *
     * @return array|false
     */
    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'class_id'))
        {
            return false;
        }

        return array('xf_ct_s3_classes' => $this->_getS3ClassModel()->getClassById($id));
    }

    /**
     * Gets SQL condition to update the existing record.
     *
     * @see XenForo_DataWriter::_getUpdateCondition()
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'class_id = ' . $this->_db->quote($this->getExisting('class_id'));
    }

    /**
     * Get the enlistment model.
     *
     * @return CavTools_Model_S3Event
     */
    protected function _getS3ClassModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_S3Class' );
    }
}