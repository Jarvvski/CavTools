<?php

class CavTools_DataWriter_RemoveEvent extends XenForo_DataWriter
{
    const OPTION_MASS_UPDATE = 'massUpdate';

    /**
     * Title of the phrase that will be created when a call to set the
     * existing data fails (when the data doesn't exist).
     *
     * @var string
     */
    protected $_existingDataErrorPhrase = 's3_event_not_found';

    /**
     * Gets the fields that are defined for the table. See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'xf_ct_s3_events' => array(
                'event_id'               => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
                'event_title'                 => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50,
                    'requiredError'     => 'please_enter_valid_title'
                ),
                'event_date'			=> array('type' => self::TYPE_STRING, 'default' => 0),
                'rank_group_id'         => array('type' => self::TYPE_UINT, 'default' => 0),
                'display_order'         => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
                'materialized_order'    => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
            )
        );
    }

    /**
     * Gets the actual existing data out of data that was passed in. See parent for explanation.
     *
     * @param mixed
     *
     * @return array|false
     */
    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'rank_id'))
        {
            return false;
        }

        return array('xf_pe_roster_rank' => $this->_getRankModel()->getRankById($id));
    }

    /**
     * Gets SQL condition to update the existing record.
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'rank_id = ' . $this->_db->quote($this->getExisting('rank_id'));
    }

    /**
     * Gets the default options for this data writer.
     */
    protected function _getDefaultOptions()
    {
        return array(
            self::OPTION_MASS_UPDATE => false
        );
    }

    protected function _postSave()
    {
        if (!$this->getOption(self::OPTION_MASS_UPDATE))
        {
            if ($this->isChanged('display_order') || $this->isChanged('rank_group_id'))
            {
                $this->_getRankModel()->rebuildRankMaterializedOrder();
            }
        }
    }

    /**
     * @return PixelExit_Roster_Model_Rank
     */
    protected function _getRankModel()
    {
        return $this->getModelFromCache('PixelExit_Roster_Model_Rank');
    }
}