<?php

class CavTools_DataWriter_S3Event extends XenForo_DataWriter {

    /**
     * Gets the fields that are defined for the table. See parent for explanation.
     *
     * @return array
     */
    protected function _getFields() 
    {
        return array(
            'xf_ct_s3_events' => array(
                'event_id' => array('type' => self::TYPE_INT, 'autoIncrement' => true),
                'event_type' => array('type' => self::TYPE_UINT),
                'event_title' => array('type' => self::TYPE_STRING),
                'event_date' => array('type' => self::TYPE_FLOAT),
                'event_time' => array('type' => self::TYPE_FLOAT),
                'event_game' => array('type' => self::TYPE_STRING),
                'event_text' => array('type' => self::TYPE_STRING),
                'username' => array('type' => self::TYPE_STRING),
                'user_id' => array('type' => self::TYPE_INT),
                'hidden' => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
                'thread_id' => array('type' => self::TYPE_INT),
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
        if (!$id = $this->_getExistingPrimaryKey($data, 'event_id'))
        {
            return false;
        }

        return array('xf_ct_s3_events' => $this->_getS3EventModel()->getEventById($id));
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
        return 'event_id = ' . $this->_db->quote($this->getExisting('event_id'));
    }

    /**
     * Get the enlistment model.
     *
     * @return CavTools_Model_S3Event
     */
    protected function _getS3EventModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_S3Event' );
    }
}