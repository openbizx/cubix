<?xml version="1.0" standalone="no"?>
<WebPage Name="{$type_view_name}" 
	Description="{$type_view_desc}" 
	Class="WebPage" 
	Access="{$acl.access}" 
	TemplateEngine="Smarty" 
	TemplateFile="view.tpl">
   <FormReferences>
   		<Reference Name="{$default_form_name}"/>
   </FormReferences>       
</WebPage>