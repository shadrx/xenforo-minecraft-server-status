<?php
/**
 * Class HeroDev_ServerStatus_Model_MinecraftServer
 */
class HeroDev_MinecraftStatus_Model_MinecraftServer extends Xenforo_Model{

    /**
     * Returns an array of the default values a new minecraft server would include.
     * @return array
     */
    public function getDefaultMinecraftServer() {
        return array (
            'minecraft_server_id' => 0,

            'name' => '',

            'address' => '',
            'query_port' => 25565,

            'active' => 1,
            'display_order' => 1,

            'query_data' => '',
            'query_type' => 'full_status'
        );
    }


    /**
     * Gets a minecraft server by ID.
     *
     * @param integer $minecraftServerId
     *
     * @return array|false
     */
    public function getMinecraftServerById($minecraftServerId)
    {
        return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_herodev_minecraft_server
			WHERE minecraft_server_id = ?
		', $minecraftServerId);
    }

    /**
     * Fetch all servers from the database
     *
     * @return array
     */
    public function getAllMinecraftServers()
    {
        return $this->fetchAllKeyed('
			SELECT *
			FROM xf_herodev_minecraft_server
			ORDER BY display_order
		', 'minecraft_server_id');
    }

    /**
     * Fetch all active servers from the database
     *
     * @return array
     */
    public function getAllActiveMinecraftServers()
    {
        return $this->fetchAllKeyed('
			SELECT *
			FROM xf_herodev_minecraft_server
			WHERE active = 1
			ORDER BY display_order
		', 'minecraft_server_id');
    }

    /**
     * Gets the possible query types.
     *
     * @return array [group] => keys: value, label, hint (optional)
     */
    public function getQueryTypes()
    {
        return array(
            'full_status' => array(
                'value' => 'full_status',
                'label' => new XenForo_Phrase('full_status'),
                'hint' => new XenForo_Phrase('full_status_hint')
            ),
            'short_status' => array(
                'value' => 'short_status',
                'label' => new XenForo_Phrase('short_status'),
                'hint' => new XenForo_Phrase('short_status_hint')
            ),
            'serverlistping' => array(
                'value' => 'serverlistping',
                'label' => new XenForo_Phrase('serverlistping'),
                'hint' => new XenForo_Phrase('serverlistping_hint')
            )
        );
    }


    /**
     * Prepares a list of minecraft servers for display.
     *
     * @param array $minecraftServers
     *
     * @return array
     */
    public function prepareMinecraftServers(array $minecraftServers)
    {
        foreach ($minecraftServers AS &$minecraftServer)
        {
            $minecraftServer = $this->prepareMinecraftServer($minecraftServer);
        }

        return $minecraftServers;
    }

    public function prepareMinecraftServer($minecraftServer){
        //Probably not the best way to do this, but it works. Move everything in query_data up one dimension in the array.
        $queryData =  unserialize($minecraftServer['query_data']);
        unset($minecraftServer['query_data']);
        $minecraftServer =  array_merge($minecraftServer, $queryData);
        return $minecraftServer;
    }

    /**
     * This function queries all the minecraft servers and updates their status in the database
     */
    public function queryMinecraftServers(){
        $db = $this->_getDb();
        $minecraftServers = $this->getAllMinecraftServers();

        foreach($minecraftServers as $minecraftServer){
           $this->queryMinecraftServer($minecraftServer);
        }
    }


    /**
     * Queries a minecraft server to get it's status
     * @param $minecraftServer
     */
    public function queryMinecraftServer($minecraftServer){
        if(is_int($minecraftServer)){
            $minecraftServer = $this->getMinecraftServerById($minecraftServer);
        }

        /* @var $dw HeroDev_MinecraftStatus_DataWriter_MinecraftServer */
        $queryHelper = new HeroDev_MinecraftStatus_Helper_MinecraftQueryHelper();

        $queryHelper->queryMinecraftServer($this->_getDb(), $minecraftServer);
    }



}