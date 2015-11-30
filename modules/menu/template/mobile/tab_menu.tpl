<ul data-role="listview" data-inset="true">	
	{foreach item=item from=$widget.menu}
		{assign var='current' value='0'}
		{foreach item=bc from=$widget.breadcrumb}
			{if $item->recordId == $bc->recordId}
	    		{assign var='current' value='1'}
			{/if}
	    {/foreach}
	    {if $current==1}
	    	<li><a class="current"  href="#app_menus_page" >{$item->objectName}</a></li>
	    {else}
	    	<li><a href="{$item->url}" >{$item->objectName}</a></li>
	    {/if}
	{/foreach}
</ul>