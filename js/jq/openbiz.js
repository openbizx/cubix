/**
 * Openbizx browser javascript library
 * @author rockys swen
 */
var Openbizx =
{
    appHome: null,
    appUrl: null,
    currentView: null,
    formInstances: new Array(),
    activeForm: null,
    debug: false,

    init: function()
    {
        if (APP_URL!=null && APP_CONTROLLER!=null) {
            Openbizx.appUrl = APP_URL;
            Openbizx.appHome = APP_CONTROLLER;
            Openbizx.currentView = APP_VIEWNAME;
            return;
        }
        $('script').each (function(script) {
	        if (script.src.endsWith("js/openbiz.js"))
	        {
	        	// extract appHome. e.g. appHome /ob/cubi/ if see /ob/cubi/js/prototype.js
	        	openbizJs = script.src;
	        	Openbizx.appUrl = openbizJs.replace("/js/openbiz.js","");
	        	Openbizx.appHome = Openbizx.appUrl + "/bin/controller.php";
	        	return;
	        }
		});
    },    
    getFormObject: function(formName)
    {
        if (Openbizx.formInstances[formName])
            return Openbizx.formInstances[formName];

        // check opener window
        if (window.opener && window.opener.window.Openbizx) {
            formObj = window.opener.window.Openbizx.formInstances[formName]
            if (formObj) {
                return formObj;
            }
        }
        try {
            // check top window
            
            if (window.top.window.Openbizx) {
                formObj = window.top.window.Openbizx.formInstances[formName]
                if (formObj) {
                    return formObj;
                }
            }
        } catch(e){
            alert("Unable to get object from class "+className+". "+e);
        };
    },
    newFormObject: function(formName, className, subForms)
    {
    	if (!className) return;
		if (Openbizx.formInstances[formName])
			delete Openbizx.formInstances[formName];
        try
        {        	
            //var newobj  = eval("new "+className+"('"+formName+"','"+subForms+"')");
            var NewClass = stringToFunction(className);
            var newobj = new NewClass(formName, subForms);
            if (newobj)
                this.formInstances[formName] = newobj;
        }
        catch(e) {
            alert("Unable to create object from class "+className+". "+e);
        }
    },
    CallFunction: function(form_method_params, options)
    {
        functionArray = Openbizx.Util.parseCallFunction(form_method_params);
		formObj = Openbizx.getFormObject(functionArray[0]);        
		if (formObj)
		{
			method = functionArray[1];
			functionArray.shift();
			functionArray.shift();
			if (formObj[method])
	    		return formObj[method](functionArray, options);
	    	else
	    		return formObj.CallFunction(method, functionArray, options);
		}
        return null;
    },
    invoke: function(formName, action, params, type, target)
    {
    	if (type == null) 
			type = Openbizx.ActionType.RPC;
        var paramsArray = Array();
        if (params)
            paramsArray = params.split(",");
    	formObj = Openbizx.getFormObject(formName);
    	if (formObj)
    	{
    		return formObj.invoke(action, paramsArray, type, target);
    	}
    },
	switchTheme: function(themeName)
    {
    	location.href = location.href+'/theme_'+themeName; 
    }
}
// call Openbizx init method
Openbizx.init();

// utility functions
var stringToFunction = function(str) {
  var arr = str.split(".");

  var fn = (window || this);
  for (var i = 0, len = arr.length; i < len; i++) {
    fn = fn[arr[i]];
  }

  if (typeof fn !== "function") {
    throw new Error("function not found");
  }

  return  fn;
};

// utility functions
function stripAndExecuteScript(text) {
    var scripts = '';
    var cleaned = text.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(){
        scripts += arguments[1] + '\n';
        return '';
    });

    if (window.execScript){
        window.execScript(scripts);
    } else {
        var head = document.getElementsByTagName('head')[0];
        var scriptElement = document.createElement('script');
        scriptElement.setAttribute('type', 'text/javascript');
        scriptElement.innerText = scripts;
        head.appendChild(scriptElement);
        head.removeChild(scriptElement);
    }
    return cleaned;
};

/**
 * Openbizx action types
 */
Openbizx.ActionType =
{
	RPC: "RPC",
	PAGE: "Page",
	FORM: "Form",
	POPUP: "Popup",
	AIM: "Aim"
}

function jq(myid) { 
   return '#' + myid.replace(/(:|\.)/g,'\\$1');
}

/**
 * Openbizx Form class
 */
Openbizx.Form = jQuery.Class(
{
    init: function(name, subForms)
    {
    	this.name = name;
        this.form = $(jq(name))[0];
        this.subForms = (subForms) ? subForms.split(",") : null;
    },
    collectData: function()
    {
    	/*this.form.fire("Form:BeforePost",{formName:this.name});  // fire Form:BeforePost. observers can update values accordingly*/
    	var formData = jQuery(this.form).serialize();
    	// TODO: add __url
        return formData;
    },
    invoke: function(action, paramArray, type, target)
    {
    	paramArray.unshift(action);
        this.CallFunction('invoke', paramArray, type, target);
    },
    CallFunction: function(method, paramArray, options)
    {
        Openbizx.activeForm = this;
    	type = (options && options['type']) ? options['type'] : Openbizx.ActionType.RPC;
        this.actionType = type;
        paramArray.unshift(this.name, method);
        
        // fire Form:BeforePost. observers can update values accordingly
        $(document).trigger("Form:BeforePost");
        
        // does AJAX call
        var url = Openbizx.appHome;
        var formData = this.collectData();
        if (type === Openbizx.ActionType.RPC || type === Openbizx.ActionType.DIALOG || type === Openbizx.ActionType.AIM) {
            requestString = Openbizx.Util.composeRequestString("RPCInvoke", paramArray);
        } else {
            requestString = Openbizx.Util.composeRequestString("Invoke", paramArray);
        }
        url += "?"+requestString;
        if (options && options['evthdl'])
            url += "&__this="+options['evthdl'];
        
        url += "&_thisView=" + Openbizx.currentView;
        
        switch (type) {
            case Openbizx.ActionType.PAGE:
                Openbizx.Net.loadPage(url+'&'+formData); break;
            case Openbizx.ActionType.FORM:
                this.submit(url); break;
            case Openbizx.ActionType.POPUP:
                Openbizx.Window.openPopup(url+'&'+formData); break;
            case Openbizx.ActionType.AIM:
                Openbizx.Net.postFile(url, this.form, formData); break;
            default:
            	if (this.hasFileToUpload())
            		Openbizx.Net.postFile(url, this.form, formData);
            	else
            		Openbizx.Net.post(url, formData);
        }
    },
    DeleteRecord: function(paramArray, options)
    {
    	alertMsg = "Are you sure you want to delete this record?";
        if (!confirm(alertMsg))
    		return;
    	this.CallFunction("deleteRecord", paramArray, options);
    },
    submit: function(url)
    {
        this.form.method = "post";
        this.form.action = url;
        this.form.submit();
    },
    focusFirst: function()
    {
    	// focus on first element
        $(this.name).getElements().each(function(input) {
            if (input.type != 'button' && !inputs.disabled) {
                inputs.focus();
                return;
            }
        })
    },
    hasFileToUpload: function()
    {
    	// check if the form has File element
    	hasFileInput = false;
    	try{
	        $(this.name).getInputs('file').each(function(input) {
	        	if (input.value != "")
	        		hasFileInput = true;
	        })
    	}catch(e){
    		
    	}
        return hasFileInput;
    },
    // callback functions
    CallbackFunction: function(content)
    {
        this.updateForm(content);
    },
    updateForm: function(content)
    {
        if (content && typeof content != "string")
            return this.updateFields(content);
        //var dt = $(this.name).parentNode;
		var dt = this.form.parentNode;
    	if (dt) {
    		$(document).trigger("Form:Load");
    		//dt.update(retContent);? // update doesn't work well in IE7
    		//dt.innerHTML = content.stripScripts();
    		//content.evalScripts.bind(content).defer();
			//dt.innerHTML = stripAndExecuteScript(content);
			jQuery(dt).html(content);
        }
    },
    updateFields: function(fieldValues)
    {
    	$.each(fieldValues, function(index, tgt_ctnt) {
            $(jq(tgt_ctnt.target))[0].value = tgt_ctnt.content;
        });
    },
    displayLoading: function(show)
    {
    	if ($(jq(this.name+'.load_disp'))[0])
    	{
			show ? $(jq(this.name+'.load_disp')).show() : $(jq(this.name+'.load_disp')).hide();
    	}
    } 
});

/**
 * Openbizx Table Form class
 */
Openbizx.TableForm = Openbizx.Form.extend (
{
    init: function(name, subForms)
    {
        this._parent(name, subForms);
        this.table = this.getTable();
        this.rows = this.getRows();
        if($(jq(name+"_selected_id"))[0]){
        	this.selectedId = $(jq(name+"_selected_id")).html();
        }else{
        	this.selectedId = (this.rows.length>0) ? this.rows[0].id.replace(this.name+"-","") : null;
        }
        this.lastSelectedId = this.selectedId;
    },
    getTable: function()
    {
        _form = $(this.form);
		if (t = _form.find('#data_table')[0]) 
            return t;
        if (t = _form.find('table.form_table')[0]) 
            return t;
        if (t = $(this.name+"_data")[0]) 
            return t.find('table')[0];
        if (t = _form.find('table')[0]) 
            return t;
        alert("Not able to get the right data table of given form "+this.name);
        return t;
    },
    getRows: function()
    {
        table = this.table;
        //return (table.tHead && table.tHead.rows.length > 0) ? $A(table.tBodies[0].rows) : $A(table.rows).without(table.rows[0]);
        if (table.tHead) 
        	return (table.tBodies.length>0) ? $.makeArray(table.tBodies[0].rows) : new Array(); 
        else
        	return $.makeArray(table.rows).slice(0,1);	// TODO_JQ
    },
    SelectRecord: function(paramArray)
    {
    	var recordId = paramArray[0];
    	if(typeof forceSelectRecord !='undefined'){
    		if(forceSelectRecord!=true){
    			if (recordId == this.selectedId) return;
    		}
    	}else{
    		if (recordId == this.selectedId) return;
    	}

        // switch highlight and call server select
        if(this.selectedId){
            this.setRowStyle($(jq(this.name+"-"+this.selectedId))[0], "normal");
            this.lastSelectedId = this.selectedId;
        }
        this.setRowStyle($(jq(this.name+"-"+recordId))[0], "select");
        this.selectedId = recordId;
        
        //if (this.subForms == null)
        //	return;
        this.CallFunction("selectRecord", [recordId]);
    },
    DeleteRecord: function(paramArray, options)
    {
    	alertMsg = "Are you sure you want to delete the selected record(s)?";
        if (!confirm(alertMsg))
    		return;
    	this.CallFunction("deleteRecord", paramArray, options);
    },
    setRowStyle: function(row, cssClass)
    {
        normalStyle = $(row).attr(cssClass);
        if (!normalStyle) 
            $(row).css('background', "white");
        else 
            row.className = normalStyle;
    },
    collectData: function()
    {
    	formData = this._parent() + "&_selectedId=" + this.selectedId;
        return formData;
    }
});

/**
 * Openbizx Network/Ajax functions
 */
Openbizx.Net =
{
	requestQueue: Array(),
    post: function (url, params)
    {
		if (Openbizx.Net.requestQueue[url]==1) return;
		if (Openbizx.activeForm) { 
			Openbizx.activeForm.displayLoading(true);
			Openbizx.Net.requestQueue[url]=1;
		}
		$.post(  
            url,  
            params,  
            function(responseText){  
				delete Openbizx.Net.requestQueue[url];
				if (Openbizx.activeForm) Openbizx.activeForm.displayLoading(false); 
                Openbizx.Net.callback(responseText);  
            },  
            "html"  
        ); 
    },
    postFile: function (url, formobj, params)
    {
    	// TODO: use AIM to post file form
    	formobj.method = "post";
    	formobj.action = (url==Openbizx.appHome) ? url+"?jsrs=1" : url+"&jsrs=1";
    	formobj.enctype = "multipart/form-data";
    	formobj.encoding = "multipart/form-data";
    	AIM.submit(formobj, {
    		'onStart' : function() {
    			if (Openbizx.activeForm)
    				Openbizx.activeForm.displayLoading(true); 
    	     	return true;
    	  	},
    	  	'onComplete' : function(response){
    	  		if (Openbizx.activeForm)
    				Openbizx.activeForm.displayLoading(false); 
    	  		if (Openbizx.debug)
    	  			Openbizx.Window.debugWindow(response);
    	  		Openbizx.Net.callback(response);
    	  	}
    	});
    	//alert("submit form?");
    	formobj.submit();
    },
    callback: function(response)
    {
        this.processResponse(response);
    },
    processResponse: function(response)
    {
        if (response.replace(" ","") == "") return;
        try {
            var respJson = jQuery.parseJSON(response); //response.evalJSON();
        }
        catch (e) {
            if (response.indexOf("Parse error")>=0)
                //Openbizx.Window.openPopupT(response,'Error',500,300);
				Openbizx.Window.openErrorDialog(response,500,300);
            else
                //alert("Json error: "+e,'Error',600,500);
				Openbizx.Window.openErrorDialog(response,500,300);
            return;
        }
        for (i=0; i < respJson.length; i++) 
        {
            tgtName = respJson[i].target;
            content = respJson[i].content;
            // handle special tgtname like "ERROR", "FUNCTION", "SCRIPT"...
            switch (tgtName) 
            {
                case "ERROR":
                    Openbizx.Window.openErrorDialog(content,500,300); continue;
                case "POPUP":
                    Openbizx.Window.openPopupT(content,'Openbizx popup',600,500); continue;      
                case "DIALOG":
                    Openbizx.Window.openDialogT(content,750,400); continue;             
                case "FUNCTION":
                    eval(content); continue;
                case "SCRIPT":
                    content.evalScripts(); continue;
                default:
                    // try to call client object function               	
                    if (formObj = Openbizx.getFormObject(tgtName))
                        formObj.CallbackFunction(content);
            }
        }
		if (i==0 && response.length>0) {
			Openbizx.Window.openErrorDialog(response,500,300);
			return;
		}
    },
    loadPage: function(url, frameName)
    {
        if (!frameName)
            window.location = url;
        else
            if (frame = Openbizx.Window.findFrame(frameName))
                frame.location = url;
    },
    redirectPage: function(url)
    {
        //window.top.location.replace(url);   // no browser history change
        self.location.href = url;
    },
    loadView: function(view, frameName)
    {
        url = Openbizx.appHome+"?view="+view;
        this.loadPage(url, frameName);
    }
}

/**
 * Openbizx Popup Window, Dialog functions
 */
Openbizx.Window =
{
    openPopup: function(url, w, h)
    {
        w = w ? w : 600; h = h ? h : 500;
        var top;
        left = (screen.width) ? (screen.width-w)/2 : 0; top = (screen.height) ? (screen.height-h)/2 : 0;
        popup = window.open (url, "", 'height='+h+',width='+w+',left='+left+',top='+top+',scrollbars=0,resizable=1,status=0');
    },
    openPopupT: function(text, title, w, h)
    {
    	var top;
        w = w ? w : 600; h = h ? h : 500;
        left = (screen.width) ? (screen.width-w)/2 : 0; top = (screen.height) ? (screen.height-h)/2 : 0;
        popup = window.open("","",'height='+h+',width='+w+',left='+left+',top='+top+',scrollbars=0,resizable=1,statu=0');
        body = "<body bgcolor=#D9D9D9>"+text+"</body>";
        popup.document.writeln("<head><title>"+title+"</title>"+body+"</head>");
    },
    centerPopup: function(popup, w, h)
    {
        LeftPosition = (screen.width) ? (screen.width-w)/2 : 0;
        TopPosition = (screen.height) ? (screen.height-h)/2 : 0;
        popup.resizeTo(w,h);
        popup.moveTo(LeftPosition, TopPosition);
    },
    closePopup: function()
    {
        if (window.opener) window.close();  // for popup window
        this.closeDialog();
    },
    openDialog: function(_url, w, h)
    {
        $('#modal_dialog').remove();
		var d = document.createElement('DIV');
		document.body.appendChild(d);
		$(d).attr('id', 'modal_dialog');
		options = {width:w, modal: true};
        $(d).load(_url, function() { $(this).dialog(options); });
    },
    openDialogT: function(text, w, h)
    {
        //$(document).add("<div id='_dialog'>"+text+"</div>");
		$('#modal_dialog').remove();
		var d = document.createElement('DIV');
        d.innerHTML = text;
        document.body.appendChild(d);
		$(d).attr('id', 'modal_dialog');
		$(d).attr('style', 'padding:10px');
		options = {width:w, modal: true};
		$(d).dialog(options);
    },
	openErrorDialog: function(text, w, h)
	{
		Openbizx.Window.openDialogT("<div style='padding:10px'>"+text+"</div>", w, h);
	},
    centerDialog: function(w, h)
    {    	
        //Dialog.setSize(w, h);
        //Dialog.setCenter();
    },
    closeDialog: function()
    {
        $('#modal_dialog').dialog("close");
		//Dialog.closeInfo(); // for dialog
    },
    close: function(name)
    {
        // close popups and dialogs
        this.closePopup();
        this.closeDialog();
    },
    findFrame: function(frameName)
    {
        top.frames.each(function(frame) {
            if (frame.name == frameName)
                return frame;
        })
        return null;
    },
    debugWindow: function(text)
    {
    	Openbizx.Window.openPopupT(text, "Debug Window");
    }
}

/**
 * Openbizx utility functions
 */
Openbizx.Util =
{
    setLanguage: function(lang)
    {
    },
    composeRequestString: function(func, paramArray)
    {
    	request = "";
    	if (func != null) {
    		request = "F=" + encodeURIComponent(func);
	    	if (paramArray != null){
	    	    for( var i=0; i < paramArray.length; i++ ){
	    	    	request += "&P" + i + "=[" + encodeURIComponent(paramArray[i]+'') + "]";
	    	    }
	    	} // parms
    	} // func
    	return request;
    },
    composeInvokeUrl: function(form_method_params)
    {
    	functionArray = Openbizx.Util.parseCallFunction(form_method_params);
    	url = Openbizx.appHome + "?" + Openbizx.Util.composeRequestString("Invoke", functionArray);
    	return url;
    },
    // obj_method_params is obj.method(p1,p2,..). Should use regexp instead
	parseCallFunction: function(obj_method_params)
	{
		// find the first "("
		var pos0 = obj_method_params.indexOf("(");
		var obj_method = obj_method_params.substring (0,pos0);
	
		pos0 = obj_method.lastIndexOf(".");
		// parse object name
		var obj = "NULL";
		var attachData= null;
		if (pos0>0)
			obj = obj_method.substring(0,pos0);
	
		// parse method/function name
		var pos1 = obj_method_params.indexOf("(");
		if (pos1>pos0)
		{
			var method = obj_method_params.substring(pos0+1,pos1);
			var pos2 = obj_method_params.indexOf(")");
			// get parameters
			var params = obj_method_params.substring(pos1+1,pos2);
			var paramsArray = Array();
			if (params)
			    paramsArray = params.split(",");
            paramsArray.unshift(obj,method);
            return paramsArray;
		}
		return "";
	},
	checkAll: function(ckbox, ckboxlist)
	{
		if (!ckboxlist.length)
			ckboxlist.checked = ckbox.checked;
		else
		{
			for (counter = 0; counter < ckboxlist.length; counter++)
			{
				ckboxlist[counter].checked = ckbox.checked;
			}
		}
	}
}

/*
 * Openbizx Loader to load js ondemand
 */
Openbizx.Loader = 
{
	instances: new Array(),
	loadJs: function(file)
	{
		if (this.instances[file] != null)
			return;
		var url = Openbizx.appHome+"/js/"+file; 
		document.writeln("<scri"+"pt src='"+url+"' type='text/javascript'></sc"+"ript>");
		this.instances[file] = 1;
	}
}

/**************************************************
 * Components hanlding scripts
 **************************************************/

/**
 * Context Menu
 */
Openbizx.Menu =
{
    activeMenu: null,
    show: function(e, menuId)
    {
        menuobj = $(jq(menuId))[0];
        if (!menuobj)
           return true;
        Openbizx.Menu.activeMenu = menuobj;
        //Find out how close the mouse is to the corner of the window
        var rightedge=ie5? document.body.clientWidth-event.clientX : window.innerWidth-e.clientX;
        var bottomedge=ie5? document.body.clientHeight-event.clientY : window.innerHeight-e.clientY;

        //if the horizontal distance isn't enough to accomodate the width of the context menu
        if (rightedge<menuobj.offsetWidth)
            //move the horizontal position of the menu to the left by it's width
            menuobj.style.left=ie5? document.body.scrollLeft+event.clientX-menuobj.offsetWidth+'px' : window.pageXOffset+e.clientX-menuobj.offsetWidth+'px';
        else
            //position the horizontal position of the menu where the mouse was clicked
            menuobj.style.left=ie5? document.body.scrollLeft+event.clientX+'px' : window.pageXOffset+e.clientX+'px';

        //same concept with the vertical position
        if (bottomedge<menuobj.offsetHeight)
            menuobj.style.top=ie5? document.body.scrollTop+event.clientY-menuobj.offsetHeight+'px' : window.pageYOffset+e.clientY-menuobj.offsetHeight+'px';
        else
            menuobj.style.top=ie5? document.body.scrollTop+event.clientY+'px' : window.pageYOffset+e.clientY-15+'px';
        
        menuobj.style.display='block';
        
        return false;
    },
    hide: function(menuId)
    {
        if (Openbizx.Menu.activeMenu)
            $(Openbizx.Menu.activeMenu).hide();
    }
}
/*
Openbizx.Tree =
{
    expand: function(node)
    {
    },
    collapse: function(node)
    {
    }
}
*/
/**
 * CKEditor
 */
Openbizx.CKEditor =
{
    init: function(editorId, options)
    {
        switch (options['type']) {
        case "basic": options['toolbar'] = "Basic"; break;
        case "full": 
        options['toolbar']=[
    ['Source','-','Templates'],
    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
    '/',
    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    ['Link','Unlink','Anchor'],
    ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
    '/',
    ['Styles','Format','Font','FontSize'],
    ['TextColor','BGColor'],
    ['Maximize', 'ShowBlocks','-','About']
    ];
        break;
        default:
        options['toolbar']=[
    ['Bold','Italic','Underline','Strike','Subscript','Superscript'],
    ['NumberedList','BulletedList','Outdent','Indent'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    ['Link','Unlink','Image','Flash','Table','HorizontalRule','SpecialChar','PageBreak','-','SelectAll','RemoveFormat'],
    ['Styles','Format','Font','FontSize'],
    ['TextColor','BGColor'],
    ['Maximize', 'ShowBlocks','Find','Replace','-','Source']
    ];
        }
        CKEDITOR.replace(editorId, options);
    },
    update: function()
    {
        if (window.CKEDITOR && CKEDITOR) {
            for (var i in CKEDITOR.instances) {
                CKEDITOR.instances[i].updateElement();
            }
        }
    },
    load: function()
    {
    	if (window.CKEDITOR && CKEDITOR) {
            for (var i in CKEDITOR.instances) {
            	CKEDITOR.remove(CKEDITOR.instances[i]);
            }
        }
    }
}
// observe the Form:Update custom event
$(document).bind("Form:BeforePost",Openbizx.CKEditor.update);
$(document).bind("Form:Load",Openbizx.CKEditor.load);


Openbizx.IDCardReader =
{
	initStatus: false,
    init: function(compId)
    {
    	  Openbizx.IDCardReader.lastInputTime = new Date().getTime();		  
    	  if($(compId+'_reader').className=='input_cardreader_error'){
    		  setTimeout("$('"+compId+"_reader').className='input_cardreader'",1000*2);
    	  }
    	  if(Openbizx.IDCardReader.initStatus==true){    		  
    		  return;
    	  }else{
    		  Openbizx.IDCardReader.initStatus=true;
    	  }
    	  Event.bind(document, "keypress", function(event) {		      		  
              var e = Event.element(event);
	            if (document.all){
	  	            pressedKey = event.keyCode;
	  	        } else{
	  	            pressedKey = event.which;
	  	        }
	            
	  	      if(pressedKey>=48 && pressedKey<=57){
	  	    	  
	  	    	  currentTime = new Date().getTime();
	  	    	  if((currentTime-Openbizx.IDCardReader.lastInputTime) <
	  	    	  	Openbizx.IDCardReader.interval ){
	  	    		  $(compId).value += String.fromCharCode(pressedKey);
	  	    		  $(compId+'_code').innerHTML += String.fromCharCode(pressedKey);
	  	    		  $(compId+'_reader').className = "input_cardreader_reading" ;	  	    		  
	  	    	  }else{
	  	    		  $(compId).value = String.fromCharCode(pressedKey);
	  	    		  $(compId+'_code').innerHTML = String.fromCharCode(pressedKey);
	  	    		  $(compId+'_reader').className = "input_cardreader";
	  	    		  setTimeout("Openbizx.IDCardReader.resetStatus('"+compId+"');",Openbizx.IDCardReader.interval*10);
	  	    	  }
	  	    	Openbizx.IDCardReader.lastInputTime = new Date().getTime();
	  	      }
	  	      else if(pressedKey==0)
	  	      {
	  	    	Openbizx.IDCardReader.lastInputTime = new Date().getTime();
	  	    	$(compId).value = "";
	    		$(compId+'_code').innerHTML = "";
	    		$(compId+'_reader').className = "input_cardreader";
	  	      }
          });        
    },
    resetStatus: function(compId)
    {
    	 currentTime = new Date().getTime();
	     if((currentTime-Openbizx.IDCardReader.lastInputTime) >
	    	  	Openbizx.IDCardReader.interval ){
	    	  $(compId+'_reader').className = "input_cardreader" ;
	     }
    },
    lastInputTime: new Date().getTime(),
    interval: 200
}

/**
 * AutoSuggestion
 */
Openbizx.AutoSuggest =
{
    init: function(form, method, input)
    {
		var formElementName = $(jq(input)).attr('name');
		var hiddenElementID  = formElementName + '_autocomplete_hidden';
		// change name of orig input 
		$(jq(input)).attr('name', formElementName + '_autocomplete_label');
		// create new hidden input with name of orig input 
		$(jq(input)).after("<input type=\"hidden\" name=\"" + formElementName + "\" id=\"" + hiddenElementID + "\" />");
		
		var url = Openbizx.appHome;
        url += "?"+Openbizx.Util.composeRequestString("RPCInvoke", [form,method,input]);
		
		$(jq(input)).autocomplete({
			source: function(request, response) {
				request[input] = $(jq(input)).val();
                $.ajax({
                  url: url,
                  data: request,
                  dataType: "json",
                  type: "POST",
                  success: function(data){
                      response(data);
                  }
                });
              },
			select: function( event, ui ) {
				$(jq(hiddenElementID)).val(ui.item.value);
				$(jq(hiddenElementID)).val(ui.item.value);
			}
		});
    }
};

/*
Openbizx.AutoSuggest =
{
    instances: new Array(),
    init: function(form, method, input, input_choice)
    {
   		if (this.instances[input])
			delete this.instances[input];
        var url = Openbizx.appHome;
        url += "?"+Openbizx.Util.composeRequestString("RPCInvoke", [form,method,input]);
        this.instances[input] = new Ajax.Autocompleter(input, input_choice, url, {afterUpdateElement:getSelectionId});
    }
}

//Support AutoSuggest where user sees one value but system submits another value.
function getSelectionId(text, li) {
    var name = text.id;
    var name_pos = name.search('_hidden');
    var hidden_name = name.substring(0,name_pos);
    if(document.getElementById(hidden_name)){
	    var hidden_obj =  document.getElementById(hidden_name);
	    hidden_obj.value = li.id;
    }
}
*/
/**
 * browser side validator
 */
Openbizx.Validator =
{
    validate: function(element, rules, alertType)
    {
    }
};

Openbizx.ImageUploader = {
	updatePreview: function(element_name){
		if(Prototype.Browser.IE){
			$(element_name+'_preview').src=$(element_name).value;
		}
	}
};

Openbizx.ImageSelector =
{
    reset: function(element)
    {
		$(jq(element)).children().css('class','normal');
    }
}

var ie5=document.all&&document.getElementById;

/**
*
* AJAX IFRAME METHOD (AIM)
* http://www.webtoolkit.info/
*
**/
AIM = {

    frame : function(c) {

        var n = 'f' + Math.floor(Math.random() * 99999);
        var d = document.createElement('DIV');
        d.innerHTML = '<iframe style="display:none" src="about:blank" id="'+n+'" name="'+n+'" onload="AIM.loaded(\''+n+'\')"></iframe>';
        document.body.appendChild(d);

        var i = document.getElementById(n);
        if (c && typeof(c.onComplete) == 'function') {
            i.onComplete = c.onComplete;
        }

        return n;
    },

    form : function(f, name) {
        f.setAttribute('target', name);
    },

    submit : function(f, c) {
        AIM.form(f, AIM.frame(c));
        if (c && typeof(c.onStart) == 'function') {
            return c.onStart();
        } else {
            return true;
        }
    },

    loaded : function(id) {
        var i = document.getElementById(id);
        if (i.contentDocument) {
            var d = i.contentDocument;
        } else if (i.contentWindow) {
            var d = i.contentWindow.document;
        } else {
            var d = window.frames[id].document;
        }
        if (d.location.href == "about:blank") {
            return;
        }

        if (typeof(i.onComplete) == 'function') {
            try {
                i.onComplete(d.forms['jsrs_Form']['jsrs_Payload'].value);
            } 
            catch (ex)
            {
                Openbizx.Window.debugWindow(d.body.innerHTML);
            }
        }
    }
}

Element.prototype.triggerEvent = function(eventName)
{
	if (document.createEvent)
    {
        var evt = document.createEvent('HTMLEvents');
        evt.initEvent(eventName, true, true);

        return this.dispatchEvent(evt);
    }

    if (this.fireEvent)
        return this.fireEvent('on' + eventName);
}
