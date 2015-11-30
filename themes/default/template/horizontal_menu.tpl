<ul class="toplevel {$widget.css} top_menu" id="nav">
	{foreach item=item from=$widget.menu}
	<li>
	    {assign var='current' value='0'}
	    {foreach item=bc from=$widget.breadcrumb}
			{if $item->recordId == $bc->recordId}
	    		{assign var='current' value='1'}
			{/if}
	    {/foreach}
		{if $current == 1 }
			{assign var=menu_class value="current" }	
		{else}
			{assign var=menu_class value="" }
	    {/if}	
		<a class="{$menu_class}" href="{if $item->url and false}{$item->url}{else}#{/if}">
			<img class="{$item->iconCSSClass}" src="{$image_url}/{if $item->iconImage!=''}{$item->iconImage}{else}spacer.gif{/if}" />{$item->objectName}
		</a>	
		{if $item->childNodes|@count > 0}
		<ul class="secondlevel module" {if $menu_class eq 'current'}style="display:block;"{/if}>
		{foreach item=subitem from=$item->childNodes}
    		{assign var='current' value='0'}
    	    {foreach item=bc from=$widget.breadcrumb}
    			{if $subitem->recordId == $bc->recordId}
    	    		{assign var='current' value='1'}
    			{/if}
    	    {/foreach}
			{if $current == 1 }
				{assign var=submenu_class value="current" }	
			{else}
				{assign var=submenu_class value="" }
		    {/if}					
				<li><a class="{$submenu_class}" href="{$subitem->url}">{$subitem->objectName}</a></li>						
		{/foreach}	
		</ul>
		{/if}
	</li>
	{/foreach}	
</ul>
<div class="v_spacer"></div>