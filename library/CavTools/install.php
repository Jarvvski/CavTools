<?php
class CavTools_Install
{
    protected static $_db = null;
    protected static $_version = 0;

    protected static $_contentTypes = array();

    protected static $_contentTypeTables = array(
        'xf_content_type', 'xf_content_type_field',
        'xf_user_alert'
    );

    protected static function _canBeInstalled(&$error)
    {
        if (XenForo_Application::$versionId < 1030070)
        {
            $error = 'This add-on requires XenForo 1.3.0 or higher.';
            return false;
        }

        return true;
    }

    public static function install($existingAddon)
    {

        if (!self::_canBeInstalled($error))
        {
            throw new XenForo_Exception($error, true);
        }

        self::$_version = is_array($installedAddon) ? $installedAddon['version_id'] : 0;

        self::stepTables();
        self::stepVersionAlters();

        if (self::$_version < 1000370)
        {
            XenForo_Model::create('PixelExit_Roster_Model_RosterAward')->rebuildAwardMaterializedOrder();
            XenForo_Model::create('PixelExit_Roster_Model_Position')->rebuildPositionMaterializedOrder();
        }

        //self::stepCoreAlters();
        //self::stepData();
        //self::stepDeleteObsolete();
    }
}