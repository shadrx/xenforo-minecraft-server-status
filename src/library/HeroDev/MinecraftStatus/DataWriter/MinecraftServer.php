<?php
class HeroDev_MinecraftStatus_DataWriter_MinecraftServer extends XenForo_DataWriter{

    /**
     * Gets the fields that are defined for the table. See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'xf_herodev_minecraft_server' => array(
                'minecraft_server_id'     => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
                'name'         => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 75, 'requiredError' => 'please_enter_valid_title'),
                'address'      => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 100, 'requiredError' => 'Please enter a valid address'),
                'query_port'     => array('type' => self::TYPE_UINT, 'required' => true, 'maxLength' => 5 , 'requiredError' => 'Please enter a valid port'),
                'active'        => array('type' => self::TYPE_BOOLEAN, 'default' => 1),
                'display_order' => array('type' => self::TYPE_UINT,   'default' => 1),
                'query_data'         => array('type' => self::TYPE_SERIALIZED, 'default' => ''),
                'last_query_date'              => array('type' => self::TYPE_UINT, 'default' => 0),
                'query_type'         => array('type' => self::TYPE_STRING, 'default' => 'gamespy4',
                    'allowedValues' => array('full_status', 'short_status' ,'serverlistping')
                ),
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
        if (!$id = $this->_getExistingPrimaryKey($data))
        {
            return false;
        }

        return array('xf_herodev_minecraft_server' => $this->_getMinecraftServerModel()->getMinecraftServerById($id));
    }

    /**
     * Gets SQL condition to update the existing record.
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'minecraft_server_id = ' . $this->_db->quote($this->getExisting('minecraft_server_id'));
    }

    protected function _postSave()
    {
      $this->_getMinecraftServerModel()->queryMinecraftServer($this->get('minecraft_server_id'));
    }


    /**
     * @return HeroDev_MinecraftStatus_Model_MinecraftServer
     */
    protected function _getMinecraftServerModel()
    {
        return $this->getModelFromCache('HeroDev_MinecraftStatus_Model_MinecraftServer');
    }
}