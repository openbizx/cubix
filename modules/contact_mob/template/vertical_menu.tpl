<ul data-role="listview" data-inset="true">
	{foreach item=item from=$widget.menu}
		{if $item->childNodes|@count > 0}
		<li data-role="list-divider">{$item->objectName}</li>
		{foreach item=subitem from=$item->childNodes}
			{assign var='current' value='0'}
    	    {foreach item=bc from=$widget.breadcrumb}
    			{if $subitem->recordId == $bc->recordId}
    	    		{assign var='current' value='1'}
    			{/if}
    	    {/foreach}
            <li><a href="{if $subitem->url}{$subitem->url}{else}javascript:{/if}">{$subitem->objectName}</a></li>
		{/foreach}	
		{/if}
	{/foreach}
</ul>