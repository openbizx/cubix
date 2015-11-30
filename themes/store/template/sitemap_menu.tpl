<form id="{$form.name}" name="{$form.name}">

<div class="newapp_bg_warper">
<div class="newapp_bg" style="height:auto">
{include file="system_appbuilder_btn.tpl.html"}
<div style="height:110px;">
	<div class="newapp_title" style="width:auto;">
		<h2 style="width:auto;padding-right:20px;">{$widget.title}</h2>			
	</div>
	{if $widget.description}
	<div class="newapp_desc">
		
		<p class="input_row" style="line-height:18px;padding-bottom:0px;color:#666666;">		
		<span>{$widget.description|replace:'\n':'<br/>'}</span>
		</p>		
	</div>
	{/if}
</div>
	<div class="upline underline" style="padding-top:10px;padding-bottom:10px;min-height:210px;height:auto;">
	{assign var='i' value=0}
	<table class="dashboard" >
	{foreach item=item from=$widget.menu}
	  {if $i % 3 == 0}
	     <tr>
	  {/if}
	  	<td valign="top">
	  		<div class="{$item->iconCSSClass}">
				<h3>{$item->objectName}</h3>
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
	<div  style="height:40px; padding-top:40px;color:#999999;font-size: 11px;padding-left: 10px;padding-top: 5px;">
	{t}The application is designed for Openbizx Cubi Platform.{/t}
	</div>
</div>
</div>
</form>		