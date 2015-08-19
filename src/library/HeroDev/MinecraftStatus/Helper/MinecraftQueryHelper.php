<?php
class MinecraftQueryException extends Exception
{
}


class HeroDev_MinecraftStatus_Helper_MinecraftQueryHelper
{

    /**
     * Queries the minecraft server specified and returns an array with information on the server
     * @param  Zend_Db_Adapter_Abstract $db
     * @param $minecraftServer
     */
    public function queryMinecraftServer($db, $minecraftServer)
    {
        $status = array();

        switch ($minecraftServer['query_type']) {
            case 'full_status':
                $query = new HeroDev_MinecraftStatus_Helper_GS4QueryHelper();
                try {
                    $query->connect($minecraftServer['address'], $minecraftServer['query_port']);

                    $status = $query->getLongStatus();


                    if (isset($status['playerList'])) {
                        //Try to generate a profile link for every player from their minecraft username. We do this now to minimize queries later on.
                        foreach ($status['playerList'] as $key => $player) {
                            $status['playerList'][$key] = array('username' => $player, 'profileLink' => self::getUserHref($player));
                        }
                    }

                    $status = array_merge($status, array("online" => 1));
                } catch (GS4QueryException $e) {
                    $status = array("online" => 0, "error" => $e->getMessage());
                }
                break;
            case 'short_status':
                $query = new HeroDev_MinecraftStatus_Helper_GS4QueryHelper();
                try {
                    $query->connect($minecraftServer['address'], $minecraftServer['query_port']);

                    $status = $query->getShortStatus();
                    $status = array_merge($status, array("online" => 1));
                } catch (GS4QueryException $e) {
                    $status = array("online" => 0, "error" => $e->getMessage());
                }
                break;
            case 'serverlistping':
                $query = new HeroDev_MinecraftStatus_Helper_ServerListPingHelper();
                try {
                    $status = $query->pingServer($minecraftServer['address'], $minecraftServer['query_port']);
                    $status = array_merge($status, array("online" => 1));
                } catch (ServerListPingException  $e) {
                    $status = array("online" => 0, "error" => $e->getMessage());
                }
                break;
        }

        //Update the status data
        $db->update('xf_herodev_minecraft_server', array(
                'query_data' => serialize($status)
            ), 'minecraft_server_id = ' . $db->quote($minecraftServer['minecraft_server_id']));

        //Update the last query time
        $db->update('xf_herodev_minecraft_server', array(
                'last_query_date' => XenForo_Application::$time
            ), 'minecraft_server_id = ' . $db->quote($minecraftServer['minecraft_server_id']));

        return $status;
    }

    /**
     * Constructs ' href="link-to-user"' if appropriate
     *
     * @param array $user
     * @param array $attributes
     *
     * @return string ' href="members/example-user.234"' or empty
     */
    public static function getUserHref($user)
    {
        /** @var XenForo_Model_User $userModel */
        $userModel = XenForo_Model::create('XenForo_Model_User');
        $user = $userModel->getUserByName($user);

        if ($user) {
            $href = self::link('members', $user);
        } else {
            $href = '';
        }

        return ($href ? "href={$href}" : '');
    }

    /**
     * Generates a link to the specified type of public data.
     *
     * @param string $type Type of data to link to. May also include a specific action.
     * @param mixed $data Primary data about this link
     * @param array $extraParams Extra named params. Unhandled params will become the query string
     * @param callback|false $escapeCallback Callback method for escaping the link
     *
     * @return string
     */
    public static function link($type, $data = null, array $extraParams = array(), $escapeCallback = 'htmlspecialchars')
    {
        $link = XenForo_Link::buildPublicLink($type, $data, $extraParams);
        if ($escapeCallback == 'htmlspecialchars') {
            $link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        } else if ($escapeCallback) {
            $link = call_user_func($escapeCallback, $link);
        }

        return $link;
    }


}