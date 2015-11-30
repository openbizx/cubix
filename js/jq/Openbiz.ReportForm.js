/**
 * Openbizx Report Form class
 */
Openbizx.ReportForm = Class.create(Openbizx.TableForm,
{
	initialize: function($super, name, subForms)
    {
        $super(name, subForms);
        var formNameArr = this.name.split("--"); 
        this.baseFormName = formNameArr[0];
        this.reportFormName = formNameArr[1];
    },
	CallFunction: function(method, paramArray, options)
    {
        Openbizx.activeForm = this;
    	type = (options && options['type']) ? options['type'] : Openbizx.ActionType.RPC;
        this.actionType = type;
        paramArray.unshift(this.baseFormName, method);	//paramArray.unshift(this.name, method);

        // does AJAX call
        var url = Openbizx.appHome;
        var formData = this.collectData();
        if (type == Openbizx.ActionType.RPC || type == Openbizx.ActionType.DIALOG)
            requestString = Openbizx.Util.composeRequestString("RPCInvoke", paramArray);
        else
            requestString = Openbizx.Util.composeRequestString("Invoke", paramArray);
        url += "?"+requestString;
        if (options && options['evthdl'])
            url += "&__this="+options['evthdl'];
	   /*
        // append report form name in url
        url += "&__form="+this.reportFormName;
		
		// append report view name in url
        url += "&__view="+getReportViewId();
        */
	    switch (type) {
            case Openbizx.ActionType.PAGE:
                Openbizx.Net.loadPage(url); break;
            case Openbizx.ActionType.FORM:
                this.submit(url); break;
            case Openbizx.ActionType.POPUP:
                Openbizx.Window.openPopup(url); break;
            default:
            	if (this.hasFileToUpload())
            		Openbizx.Net.postFile(url, this.form, formData);
            	else
            		Openbizx.Net.post(url, formData);
        }
    },
	collectData: function($super)
    {
    	formData = $super();
		// append report form name in url
        formData += "&__form="+this.reportFormName;
		// append report view name in url
        formData += "&__view="+getReportViewId();
        return formData;
    }
});

function getReportViewId()
{
	return $('report_view_id').innerHTML;
}

/**
 * Openbizx Pivot Form class
 */
Openbizx.PivotForm = Class.create(Openbizx.Form,
{
	initialize: function($super, name, subForms)
    {
        $super(name, subForms);
	},
    renderPivot: function(paramArray, options)
    {
        if (validatePivotForm()) {
			this.form.setAttribute("target", "_blank");
            this.CallFunction("renderPivot", paramArray, options);
		}
    },
	collectData: function($super)
    {
    	formData = $super();
		// append report view name in url
        formData += "&__view="+getReportViewId();
        return formData;
    }
});

// validation of input
var pivotInputs = ['fld_colfld1','fld_colfld2','fld_rowfld1','fld_rowfld2','fld_rowfld3','fld_datafld1'];
function validatePivotInputs(elem) {
    for(i=0; i<pivotInputs.length; i++) {
        if (elem.id != pivotInputs[i] && elem.value != '' && elem.value == $(pivotInputs[i]).value) {
            select_list_selected_index = elem.selectedIndex;
            text = elem.options[select_list_selected_index].text
            alert("Please select a different field other than '"+text+"'");
            elem.value='';
        }
    }
}
function validatePivotForm() {
    if ($('fld_colfld1').value == '' || $('fld_rowfld1').value == '' || $('fld_datafld1').value == '') {
        alert("Please select a valid column field, row fields and data field for pivot table.");
        return false;
    }
    return true;
}