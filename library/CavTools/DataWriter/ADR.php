<?php

class CavTools_DataWriter_ADR extends XenForo_DataWriter {

    /**
     * Gets the fields that are defined for the table. See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'xf_ct_adr' => array(
                'adr_id' => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
                'text' => array('type' => self::TYPE_STRING),
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
        if (!$id = $this->_getExistingPrimaryKey($data, 'adr_id'))
        {
            return false;
        }

        return array('xf_ct_adr' => $this->_getADRModel()->getTextById($id));
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
        return 'adr_id = ' . $this->_db->quote($this->getExisting('adr_id'));
    }

    /**
     * Get the ADR model.
     *
     * @return CavTools_Model_ADR
     */
    protected function _getADRModel()
    {
        return $this->getModelFromCache ( 'CavTools_Model_ADR' );
    }
}