<!-- APPENDED_SCRIPT -->
{Openbizx\Openbizx::$app->getClientProxy()->appendScripts('cookies','cookies.js')}
{Openbizx\Openbizx::$app->getClientProxy()->appendScripts('general_ui','general_ui.js')}

<!-- APPENDED_STYLE -->
{Openbizx\Openbizx::$app->getClientProxy()->appendStyles('general','general.css')}
{Openbizx\Openbizx::$app->getClientProxy()->appendStyles('system_backend','system_backend.css')}
{Openbizx\Openbizx::$app->getClientProxy()->appendStyles('system_menu_icons','system_menu_icons.css')}
{Openbizx\Openbizx::$app->getClientProxy()->appendStyles('system_dashboard_icons','system_dashboard_icons.css')}

{include file='system_view.tpl.html'}
