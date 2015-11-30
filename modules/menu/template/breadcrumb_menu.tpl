<div style="padding-left:20px;">
<a href="javascript:"><img class="icon_dot_root" style="margin-top:5px;" border="0" src="{$image_url}/spacer.gif" />{t}Cubi System{/t}</a>
	{foreach item=item from=$widget.breadcrumb}
		{if $item->url !=""}
		<a href="{$item->url}">
		{else}
		<a href="javascript:">
		{/if}
		<img class="icon_dot" border="0" src="{$image_url}/spacer.gif" />{$item->objectName}</a>	    
	{/foreach}
</div>