<?php
/**
 * Route prefix handler for addons in the admin control panel.
 *
 * @package XenForo_AddOns
 */
class HeroDev_MinecraftStatus_Route_PrefixAdmin_MinecraftServers implements XenForo_Route_Interface
{
    /**
     * Match a specific route for an already matched prefix.
     *
     * @see XenForo_Route_Interface::match()
     */
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'minecraft_server_id');
        return $router->getRouteMatch('HeroDev_MinecraftStatus_ControllerAdmin_MinecraftServer', $action, 'minecraftServers');
    }

    /**
     * Method to build a link to the specified page/action with the provided
     * data and params.
     *
     * @see XenForo_Route_BuilderInterface
     */
    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'minecraft_server_id', 'name');
    }
}