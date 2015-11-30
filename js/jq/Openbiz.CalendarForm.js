/**
 * Openbizx Calendar Form class
 */
Openbizx.CalendarForm = Openbizx.Form.extend (
{
	collectData: function()
    {
    	formData = this._parent();
        formData = formData + "&_selectedId=" + this.selectedId
    						+ "&dayDelta=" + this.dayDelta
    						+ "&minuteDelta=" + this.minuteDelta
    						+ "&allDay=" + this.allDay
    						+ "&updateType=" + this.updateType
    						;
        return formData;
    }
});