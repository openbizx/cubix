<form id="{$form.name}" name="{$form.name}">

<div style="padding-left:25px; padding-right:20px;">
{include file="system_appbuilder_btn.tpl.html"}
	<div>
		<div ><h2>{$widget.title}</h2></div>
	</div>
		
		{if $widget.description}
		<p class="input_row" style="line-height:20px;padding-bottom:20px;">		
		<span>{$widget.description}</span>
		</p>
		{else}
		<div style="height:15px;"></div>
		{/if}

	{assign var='i' value=0}
	<table class="dashboard" >
	{foreach item=item from=$widget.menu}
	  {if $i % 3 == 0}
	     <tr>
	  {/if}
	  	<td valign="top">
	  		<div class="{$item->iconCSSClass}">
				<h3>{$item->objectName}</h3>
				<p>{$item->objectDescription}</p>	
				{if $item->childNodes|@count > 0}
				<ul>
				{foreach item=subitem from=$item->childNodes}													
					<li><a href="{if $subitem->url}{$subitem->url}{else}javascript:{/if}">{$subitem->objectName}</a></li>					
				{/foreach}	
				</ul>
				{assign var='i' value=$i+1}	
				{/if}
			</div>
	  	</td>
	  {if $i % 3 == 0}
	     <tr>
	  {/if}
	{/foreach}
	</table>
</div>

</form>		