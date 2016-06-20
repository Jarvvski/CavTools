<?php
class CavTools_Install {

    protected static $table = array(
        'createEnlistments' => 'CREATE TABLE IF NOT EXISTS `xf_ct_rrd_enlistments` (             
                `enlistment_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT( 10 ) UNSIGNED NOT NULL,
                `recruiter` VARCHAR( 50 ),
                `last_name` VARCHAR( 50 ) NOT NULL,
                `first_name` VARCHAR( 50 ) NOT NULL,
                `age` INT( 10 ) UNSIGNED NOT NULL,
                `timezone` VARCHAR( 10 ) NOT NULL,
                `enlistment_date` BIGINT ( 20 ) NOT NULL,
                `steamID` BIGINT ( 50 ) UNSIGNED NOT NULL,
                `in_clan` VARCHAR ( 10 ) NOT NULL,
                `past_clans` VARCHAR( 50 ),
                `game` VARCHAR( 50 ) NOT NULL,
                `reenlistment` TINYINT ( 1 ) NOT NULL,
                `hidden` TINYINT( 1 ) NOT NULL,
                `thread_id` INT ( 10 ) NOT NULL,
                `vac_ban` TINYINT( 1 ) NOT NULL,
                `under_age` TINYINT ( 1 ) NOT NULL,
                PRIMARY KEY (`enlistment_id`)
                )
            ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;',
        'dropEnlistments' => 'DROP TABLE IF EXISTS `xf_ct_rrd_enlistments`',
        
        'createRRDLogs' => 'CREATE TABLE IF NOT EXISTS `xf_ct_rrd_logs` (             
                `log_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
                `enlistment_id` INT( 10 ) UNSIGNED NOT NULL,
                `user_id` INT ( 10 ) NOT NULL ,
                `username` VARCHAR (50) NOT NULL ,
                `log_date` BIGINT ( 20 ) NOT NULL,
                `action_taken` VARCHAR (50) NOT NULL,
                PRIMARY KEY (`log_id`)
                )
            ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;',
        'dropRRDLogs' => 'DROP TABLE IF EXISTS `xf_ct_rrd_enlistments`'
    );

    // This is the function to create a table in the database so our addon will work.
    public static function install()
    {
        $db = XenForo_Application::getDb();
        $db->query(self::$table['createEnlistments']);
        $db->query(self::$table['createRRDLogs']);
    }

    // This is the function to DELETE the table of our addon in the database.
    public static function uninstall()
    {
        $db = XenForo_Application::getDb();
        $db->query(self::$table['dropEnlistments']);
        $db->query(self::$table['dropRRDLogs']);
    }
}

?>