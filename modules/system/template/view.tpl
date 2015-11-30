{Openbizx\Openbizx::$app->getClientProxy()->appendScripts('cookies','cookies.js')}
{Openbizx\Openbizx::$app->getClientProxy()->appendScripts('general_ui','general_ui.js')}

{Openbizx\Openbizx::$app->getClientProxy()->appendStyles('general','general.css')}
{Openbizx\Openbizx::$app->getClientProxy()->appendStyles('system_backend','system_backend.css')}
{Openbizx\Openbizx::$app->getClientProxy()->appendStyles('system_menu_icons','system_menu_icons.css')}

{php}
$appendStyle = "
    <style>
        .detail_form_panel_padding { padding-left:10px; }
    </style>
    ";
    Openbizx\Openbizx::$app->getClientProxy()->appendStyles('on-map-view-style',$appendStyle, false);
{/php}

{include file='system_view.tpl.html'}
