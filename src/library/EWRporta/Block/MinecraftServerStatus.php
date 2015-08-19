<?php

class EWRporta_Block_MinecraftServerStatus extends XenForo_Model
{
	public function getBypass()
	{
        $minecraftServerModel = $this->_getMinecraftServerModel();

        //Get all active minecraft servers from the database
        $minecraftServers = $minecraftServerModel->prepareMinecraftServers($minecraftServerModel->getAllActiveMinecraftServers());

		return $minecraftServers;
	}


    /**
     * Creates and returns the minecraft server model
     * @return HeroDev_MinecraftStatus_Model_MinecraftServer
     */
    protected function _getMinecraftServerModel(){
        return $this->getModelFromCache('HeroDev_MinecraftStatus_Model_MinecraftServer');
    }
}