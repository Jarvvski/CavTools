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
                'user_id' => array('type' => self::TYPE_STRING, 'required' => true),
                'enlistment_date' => array('type' => self::TYPE_STRING, 'required' => true),
                'username' => array( 'type' => self::TYPE_STRING, 'required' => true),
                'last_name' => array('type' => self::TYPE_STRING, 'required' => true),
                'first_name' => array('type' => self::TYPE_STRING, 'required' => true),
                'age' => array('type' => self::TYPE_UINT, 'required' => true),
                'steamID' => array('type' => self::TYPE_STRING, 'required' => true),
                'clan' => array('type' => self::TYPE_BOOLEAN, 'required' => true),
                'orders' => array('type' => self::TYPE_BOOLEAN, 'required' => true),
                'game' => array('type' => self::TYPE_STRING, 'required' => true),
                'enlistment_type' => array('type' => self::TYPE_BOOLEAN, 'required' => true),
                'hidden' => array('type' => self::TYPE_BOOLEAN, 'required => true', 'default' => false),
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
     * Get the simple text model.
     *
     * @return CavTools_Model_Enlistment
     */
    protected function _getEnlistmentModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_Enlistment' );
    }
}