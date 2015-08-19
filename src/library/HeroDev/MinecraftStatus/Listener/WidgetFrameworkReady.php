<?php
class HeroDev_MinecraftStatus_Listener_WidgetFrameworkReady {

    public static function registerWidgetRenderer(&$renderers){

        $renderers[] = "HeroDev_MinecraftStatus_WidgetRenderer_Status";

    }

}