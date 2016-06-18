<?php
class CavTools_Install {

    protected static $table = array(
        'createQuery' => 'CREATE TABLE IF NOT EXISTS `xf_ct_rrd_enlistments` (             
                `enlistment_id` INT( 10 ) NOT NULL AUTO_INCREMENT,
                `user_id` INT( 10 ) UNSIGNED NOT NULL ,
                `recruiter` VARCHAR( 50 ),
                `last_name` VARCHAR( 50 ) NOT NULL,
                `first_name` VARCHAR( 50 ) NOT NULL,
                `age` VARCHAR( 50 ) NOT NULL,
                `timezone` VARCHAR( 10 ) NOT NULL,
                `enlistment_date` VARCHAR ( 20 ) NOT NULL,
                `steamID` INT( 10 ) NOT NULL,
                `in_clan` TINYINT ( 1 ) NOT NULL,
                `past_clans` VARCHAR( 50 ),
                `game` VARCHAR ( 50 ) NOT NULL,
                `reenlistment` INT ( 10 ) UNSIGNED NOT NULL,
                `hidden` TINYINT ( 1 ) NOT NULL,
                PRIMARY KEY (`enlistment_id`)
                )
            ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;',
        'dropQuery' => 'DROP TABLE IF EXISTS `xf_ct_rrd_enlistments`'
    );

    // This is the function to create a table in the database so our addon will work.
    public static function install()
    {
        $db = XenForo_Application::get('db');
        $db->query(self::$table['createQuery']);
    }

    // This is the function to DELETE the table of our addon in the database.
    public static function uninstall()
    {
        $db = XenForo_Application::get('db');
        $db->query(self::$table['dropQuery']);
    }
}

?>