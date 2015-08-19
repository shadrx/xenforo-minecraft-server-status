<?php
class HeroDev_MinecraftStatus_ControllerAdmin_MinecraftServer extends XenForo_ControllerAdmin_Abstract{


    protected function _preDispatch($action)
    {
        $this->assertAdminPermission('minecraftServer');
    }

    public function actionIndex(){

        $minecraftServerModel = $this->_getMinecraftServerModel();

        $viewParams = array(
            'minecraftServers' => $minecraftServerModel->prepareMinecraftServers($minecraftServerModel->getAllMinecraftServers()),
        );

        return $this->responseView('HeroDev_MinecraftStatus_ViewAdmin_MinecraftServer', 'herodev_minecraftstatus_minecraftserver_list', $viewParams);
    }

    protected function _getMinecraftServerAddEditResponse(array $minecraftServer)
    {
        $minecraftServerModel = $this->_getMinecraftServerModel();

        $viewParams = array(
            'minecraftServer' => $minecraftServer,
            'queryTypes' => $minecraftServerModel->getQueryTypes()
        );

        return $this->responseView('HeroDev_MinecraftStatus_ViewAdmin_MinecraftServer_Edit', 'herodev_minecraftstatus_minecraftserver_edit', $viewParams);
    }

    public function actionAdd()
    {
        return $this->_getMinecraftServerAddEditResponse($this->_getMinecraftServerModel()->getDefaultMinecraftServer());
    }

    public function actionEdit()
    {
        $minecraftServerId = $this->_input->filterSingle('minecraft_server_id', XenForo_Input::UINT);
        $minecraftServer = $this->_getMinecraftServerOrError($minecraftServerId);

        return $this->_getMinecraftServerAddEditResponse($minecraftServer);
    }

    public function actionSave(){
        $this->_assertPostOnly();

        $minecraftServerId = $this->_input->filterSingle('minecraft_server_id', XenForo_Input::UINT);

        $data = $this->_input->filter(array(
            'name' => XenForo_Input::STRING,
            'address' => XenForo_Input::STRING,
            'query_port' => XenForo_Input::UINT,
            'active' => XenForo_Input::UINT,
            'display_order' => XenForo_Input::UINT,
            'query_type' => XenForo_Input::STRING
        ));

        $dw = XenForo_DataWriter::create('HeroDev_MinecraftStatus_DataWriter_MinecraftServer');

        if($minecraftServerId){
            $dw->setExistingData($minecraftServerId);
        }

        $dw->bulkSet($data);
        $dw->save();

        $minecraftServerId = $dw->get('minecraft_server_id');

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildAdminLink('minecraft-servers') . $this->getLastHash($minecraftServerId)
        );
    }

    public function actionDelete()
    {
        $minecraftServerId = $this->_input->filterSingle('minecraft_server_id', XenForo_Input::UINT);

        if ($this->isConfirmedPost())
        {
            return $this->_deleteData(
                'HeroDev_MinecraftStatus_DataWriter_MinecraftServer', 'minecraft_server_id',
                XenForo_Link::buildAdminLink('minecraft-servers')
            );
        }
        else
        {
            $viewParams = array('minecraftServer' => $this->_getMinecraftServerOrError($minecraftServerId));

            return $this->responseView('HeroDev_MinecraftServer_ViewAdmin_MinecraftServer_Delete', 'herodev_minecraftstatus_minecraftserver_delete', $viewParams);
        }
    }

    /**
     * Selectively enables or disables specified minecraft servers
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionToggle()
    {
        return $this->_getToggleResponse(
            $this->_getMinecraftServerModel()->getAllMinecraftServers(),
            'HeroDev_MinecraftStatus_DataWriter_MinecraftServer',
            'minecraft-servers');
    }


    public function actionRefresh(){
        $this->_getMinecraftServerModel()->queryMinecraftServers();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildAdminLink('minecraft-servers')
        );
    }

    /**
     * Gets a valid minecraft server or throws an exception.
     *
     * @param integer $minecraftServerId
     *
     * @return array
     */
    protected function _getMinecraftServerOrError($minecraftServerId)
    {
        $minecraftServerModel = $this->_getMinecraftServerModel();

        $minecraftServer = $minecraftServerModel->getMinecraftServerById($minecraftServerId);
        if (!$minecraftServer)
        {
            throw $this->responseException($this->responseError(new XenForo_Phrase('requested_minecraft_server_not_found'), 404));
        }

        return $minecraftServerModel->prepareMinecraftServer($minecraftServer);
    }

    /**
     * @return HeroDev_MinecraftStatus_Model_MinecraftServer
     */
    protected function _getMinecraftServerModel()
    {
        return $this->getModelFromCache('HeroDev_MinecraftStatus_Model_MinecraftServer');
    }


}
