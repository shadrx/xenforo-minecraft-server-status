<?php
class HeroDev_MinecraftStatus_CronEntry_MinecraftQuery{

    public static function queryMinecraftServers(){
        /* @var $minecraftServerModel HeroDev_MinecraftStatus_Model_MinecraftServer */
        $minecraftServerModel = XenForo_Model::create('HeroDev_MinecraftStatus_Model_MinecraftServer');

        $minecraftServerModel->queryMinecraftServers();
    }

}