/** @param {jQuery} $ jQuery Object */
var MinecraftStatus = {};

!function($, window, document, _undefined)
{

    MinecraftStatus.MinecraftServerEdit = function($form)
    {

        $("#query_types").change(function(){
            full.hide();
        });

        $("#query_types :input").each(function(){
            var input = $(this); // This is the jquery object of the input, do what you will
            input.change(function(){
                var id = input.attr('id');
                var ctrl_query_port = $("#ctrl_query_port");
                if(id === "ctrl_query_type_serverlistping"){
                    $("label[for='ctrl_query_port']").text("Minecraft Server Port:");

                    ctrl_query_port.next().next().next().text(ctrl_query_port.data("normal_explain"));
                }else{
                    $("label[for='ctrl_query_port']").text("Query Port:");

                    ctrl_query_port.next().next().next().text(ctrl_query_port.data("query_explain"));
                }
            });
        });
    };



    XenForo.register('.MinecraftServerEdit', 'MinecraftStatus.MinecraftServerEdit');
}(jQuery, this, document);