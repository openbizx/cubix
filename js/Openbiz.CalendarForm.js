/**
 * Openbizx Calendar Form class
 */
Openbizx.CalendarForm = Class.create(Openbizx.Form,
{
	collectData: function($super)
    {
    	formData = $super() + "&_selectedId=" + this.selectedId
    						+ "&dayDelta=" + this.dayDelta
    						+ "&minuteDelta=" + this.minuteDelta
    						+ "&allDay=" + this.allDay
    						+ "&updateType=" + this.updateType
    						;
        return formData;
    }
});
