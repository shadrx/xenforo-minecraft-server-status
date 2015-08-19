<?php
class HeroDev_MinecraftStatus_WidgetRenderer_Status extends WidgetFramework_WidgetRenderer {


    /**
     * Required method: define basic configuration of the renderer.
     * Available configuration parameters:
     *    - name: The display name of the renderer
     *    - options: An array of renderer's options
     *    - useCache: Flag to determine the renderer can be cached or not
     *    - useUserCache: Flag to determine the renderer needs to be cached by an
     *                    user-basis.
     *                    Internally, this is implemented by getting the current user permission
     *                    combination id (not the user id as normally expected). This is done to
     *                    make sure the cache is used effectively
     *    - useLiveCache: Flag to determine the renderer wants to by pass writing to
     *                    database
     *                    when it's being cached. This may be crucial if the renderer does a lot
     *                    of thing on a big board. It's recommended to use a option for this
     *                    because not all forum owner has a live cache system setup
     *                    (XCache/memcached)
     *    - cacheSeconds: A numeric value to specify the maximum age of the cache (in
     *                    seconds).
     *                    If the cache is too old, the widget will be rendered from scratch
     *    - useWrapper: Flag to determine the widget should be wrapped with a wrapper.
     *                    Renderers
     *                    that support wrapper will have an additional benefits of tabs: only
     *                    wrapper-enabled widgets will be possible to use in tabbed interface
     */
    protected function _getConfiguration()
    {
        return array(
            'name' => 'Minecraft Server Status',
            'options' => array('limit' => XenForo_Input::UINT, ),
        );
    }

    /**
     * Required method: get the template title of the options template (to be used in
     * AdminCP).
     * If this is not used, simply returns false.
     */
    protected function _getOptionsTemplate()
    {
        return false; //'herodev_minecraftstatus_widget_options_status';
    }

    /**
     * Required method: get the template title of the render template (to be used in
     * front-end).
     *
     * @param array $widget
     * @param string $positionCode
     * @param array $params
     */
    protected function _getRenderTemplate(array $widget, $positionCode, array $params)
    {
        return 'herodev_minecraftstatus_widget_status';
    }

    /**
     * Required method: prepare data or whatever to get the render template ready to
     * be rendered.
     *
     * @param array $widget
     * @param string $positionCode
     * @param array $params
     * @param XenForo_Template_Abstract $renderTemplateObject
     */
    protected function _render(array $widget, $positionCode, array $params, XenForo_Template_Abstract $renderTemplateObject)
    {
        $minecraftServerModel = $this->_getMinecraftServerModel();

        //Get all active minecraft servers from the database
        $minecraftServers = $minecraftServerModel->prepareMinecraftServers($minecraftServerModel->getAllActiveMinecraftServers());
        $renderTemplateObject->setParam('minecraftServers', $minecraftServers);

       return $renderTemplateObject->render();
    }

    /**
     * Creates and returns the minecraft server model
     * @return HeroDev_MinecraftStatus_Model_MinecraftServer
     */
    protected function _getMinecraftServerModel(){
        return WidgetFramework_Core::getInstance()->getModelFromCache('HeroDev_MinecraftStatus_Model_MinecraftServer');
    }
}