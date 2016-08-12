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
                `steamID` VARCHAR ( 200 ) NOT NULL,
                `in_clan` VARCHAR ( 10 ) NOT NULL,
                `past_clans` TEXT,
                `game` VARCHAR( 50 ) NOT NULL,
                `reenlistment` TINYINT ( 1 ) NOT NULL,
                `hidden` TINYINT( 1 ) NOT NULL,
                `thread_id` INT ( 10 ) NOT NULL,
                `vac_ban` TINYINT( 1 ) NOT NULL,
                `under_age` TINYINT ( 1 ) NOT NULL,
                `current_status` TINYINT ( 1 ) NOT NULL,
                `last_update` BIGINT ( 20 ) NOT NULL,
                `rtc_thread_id` INT ( 10 ),
                `s2_thread_id` INT ( 10 ),
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
                `action_taken` VARCHAR (250) NOT NULL,
                PRIMARY KEY (`log_id`)
                )
            ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;',
        'dropRRDLogs' => 'DROP TABLE IF EXISTS `xf_ct_rrd_logs`',

        'createS3Events' => 'CREATE TABLE IF NOT EXISTS `xf_ct_s3_events` (             
                `event_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
                `event_type` INT( 10 ) UNSIGNED NOT NULL,
                `event_title` VARCHAR( 200 ) NOT NULL ,
                `event_date` BIGINT ( 20 ) NOT NULL,
                `event_time` BIGINT ( 20 ) NOT NULL,
                `event_game` VARCHAR ( 50 ) NOT NULL ,
                `event_text` LONGTEXT NOT NULL ,
                `username` VARCHAR ( 50 ) NOT NULL ,
                `user_id` INT ( 10 ) NOT NULL ,
                `hidden` TINYINT ( 50 ) NOT NULL ,
                `thread_id` INT ( 10 ) NOT NULL ,
                PRIMARY KEY (`event_id`)
                )
            ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;',
        'dropS3Events' => 'DROP TABLE IF EXISTS `xf_ct_s3_events`',

        'createS3Classes' => 'CREATE TABLE IF NOT EXISTS `xf_ct_s3_classes` (             
                `class_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
                `class_name` VARCHAR ( 50 ) NOT NULL,
                `class_text` LONGTEXT NOT NULL ,
                `username` VARCHAR( 50 ) NOT NULL ,
                `user_id` INT ( 10 ) NOT NULL ,
                `hidden` TINYINT ( 10) NOT NULL ,
                PRIMARY KEY (`class_id`)
                )
            ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;',
        'dropS3Classes' => 'DROP TABLE IF EXISTS `xf_ct_s3_classes`',

        'createADRstore' => 'CREATE TABLE IF NOT EXISTS `xf_ct_adr` (
                `adr_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
                `text` LONGTEXT NOT NULL,
                PRIMARY KEY (`adr_id`)
                )
            ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;',
        'dropADRstore' => 'DROP TABLE IF EXISTS `xf_ct_adr`'

    );

    // This is the function to create a table in the database so our addon will work.
    public static function install($addon)
    {
        if ($addon['version_id'] <= 575) {
            $db = XenForo_Application::getDb();
            $db->query(self::$table['createEnlistments']);
            $db->query(self::$table['createRRDLogs']);
            $db->query(self::$table['createS3Events']);
            $db->query(self::$table['createS3Classes']);
            $db->query(self::$table['createADRstore']);
        } else if ($addon['version_id'] <= 575) {
            $db = XenForo_Application::getDb();
            $db->query(self::$table['dropEnlistments']);
            $db->query(self::$table['createEnlistments']);
        } else if ($addon['version_id'] <= 578) {
            $db = XenForo_Application::getDb();
            $db->query(self::$table['dropADRstore']);
        }
    }

    // This is the function to DELETE the table of our addon in the database.
    public static function uninstall()
    {
        $db = XenForo_Application::getDb();
        $db->query(self::$table['dropEnlistments']);
        $db->query(self::$table['dropRRDLogs']);
        $db->query(self::$table['dropS3Events']);
        $db->query(self::$table['dropS3Classes']);
        $db->query(self::$table['dropADRstore']);
    }
}

?>
