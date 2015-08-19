<?php
class HeroDev_MinecraftStatus_Install_Controller
{
    public static function install($previousVersion)
    {
        $db = XenForo_Application::getDb();

        //Check if any requirements aren't met and notify the user
        $errors = self::getRequirementErrors($db);
        if ($errors) {
            self::responseError($errors);
        }

        //Clean install of add-on
        if (!$previousVersion) {
            $tables = self::getTables();

            foreach ($tables AS $tableSql) {
                try {
                    $db->query($tableSql);
                } catch (Zend_Db_Exception $e) {}
            }
        }
        
        //Prevent errors from showing up due to changes in query arrays
        HeroDev_MinecraftStatus_CronEntry_MinecraftQuery::queryMinecraftServers();
    }

    /**
     * The function called by XenForo when uninstalling this add-on in the control panel
     */
    public static function uninstall()
    {
        $db = XenForo_Application::get('db');

        foreach (self::getTables() AS $tableName => $tableSql) {
            try {
                $db->query("DROP TABLE IF EXISTS `$tableName`");
            } catch (Zend_Db_Exception $e) {
            }
        }
    }

    /**
     * A helper function which returns an array of the sql tables needed for this add-on to function
     */
    public static function getTables()
    {
        $tables = array();

        $tables['xf_herodev_minecraft_server'] = "
                CREATE TABLE IF NOT EXISTS `xf_herodev_minecraft_server` (
                `minecraft_server_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(75) NOT NULL DEFAULT '',
                `address` varchar(100) NOT NULL DEFAULT '',
                `query_port` int(5) NOT NULL,
                `active` tinyint(3) NOT NULL,
                `display_order` int(10) NOT NULL DEFAULT '1',
                `query_data` mediumblob NOT NULL,
                `last_query_date` int(10) unsigned NOT NULL DEFAULT '0',
                `query_type` enum('full_status','short_status','serverlistping','') NOT NULL DEFAULT 'full_status',
                PRIMARY KEY (`minecraft_server_id`),
                KEY `display_order` (`display_order`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ";

        return $tables;
    }

    private static function getRequirementErrors(Zend_Db_Adapter_Abstract $db = null)
    {
        $errors = array();

        if (XenForo_Application::$versionId < 1020070) {
            $errors['xenforoVersion'] = "This add-on requires XenForo 1.2.0 or higher.";
        }

        //Check if socket support is available
        if (!function_exists('fwrite') || !function_exists('fsockopen') || !function_exists('fread')) {
            $errors['socketSupport'] = "The function family: fwrite, fsockopen and fread must be enabled. Please contact your host or send a PM to shadrxninga for support";
        }

        if ($db) {
            //Versions prior to 2.2.0 need to be uninstalled before this version will work.
            $addon_installed = $db->fetchRow("SELECT version_id, version_string FROM `xf_addon` WHERE `addon_id` = 'HeroDev_ServerStatus'");

            if ($addon_installed) {
                $errors['oldVersion'] = "You've still got version " . $addon_installed['version_string'] . " of this add-on installed. You'll need to uninstall it before continuing! <img width='60%' height= '60%' src='http://shadrx.com/addons/herodev/minecraftstatus/install/uninstall_old.png'/>";
            }
        }

        return $errors;
    }


    /**
     * Throws an exception which shows the user any errors/requirements passed to it.
     *
     * @param $errors
     * @throws XenForo_Exception
     */
    public static function responseError($errors)
    {

        $output = "<h1>Requirements Check</h1>
                       <b>The following needs to be done in order to install this add-on!</b> Once you've met the following, the add-on will install.<br />
                       <ul>";

        foreach ($errors as $error) {
            $output .= "<li>" . $error . "</li>";
        }

        $output .= "</ul>";

        throw new XenForo_Exception($output, true);
    }


}
