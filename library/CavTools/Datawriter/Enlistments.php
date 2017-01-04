<?php

class CavTools_DataWriter_Enlistments extends XenForo_DataWriter {

    /**
     * Gets the fields that are defined for the table. See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'xf_ct_rrd_enlistments' => array(
                'enlistment_id' => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
                'user_id' => array('type' => self::TYPE_STRING),
                'recruiter' => array('type' => self::TYPE_STRING),
                'last_name' => array('type' => self::TYPE_STRING),
                'first_name' => array('type' => self::TYPE_STRING),
                'age' => array('type' => self::TYPE_UINT),
                'timezone' => array('type' => self::TYPE_STRING),
                'enlistment_date' => array('type' => self::TYPE_FLOAT),
                'steamID' => array('type' => self::TYPE_FLOAT),
                'origin' => array('type' => self::TYPE_STRING),
                'in_clan' => array('type' => self::TYPE_BOOLEAN),
                'past_clans' => array('type' => self::TYPE_STRING),
                'game' => array('type' => self::TYPE_STRING),
                'reenlistment' => array('type' => self::TYPE_BOOLEAN),
                'hidden' => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
                'thread_id' => array('type' => self::TYPE_INT),
                'vac_ban' => array('type' => self::TYPE_UINT),
                'under_age' => array('type' => self::TYPE_UINT),
                'current_status' => array('type' => self::TYPE_UINT),
                'last_update' => array('type' => self::TYPE_FLOAT),
                'rtc_thread_id' => array('type' => self::TYPE_INT),
                's2_thread_id' => array('type' => self::TYPE_INT),
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
        if (!$id = $this->_getExistingPrimaryKey($data, 'enlistment_id'))
        {
            return false;
        }

        return array('xf_ct_rrd_enlistments' => $this->_getEnlistmentModel()->getEnlistmentById($id));
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
        return 'enlistment_id = ' . $this->_db->quote($this->getExisting('enlistment_id'));
    }

    /**
     * Get the enlistment model.
     *
     * @return CavTools_Model_Enlistment
     */
    protected function _getEnlistmentModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_Enlistment' );
    }
}
