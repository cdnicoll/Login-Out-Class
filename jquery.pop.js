var jQ = jQuery.noConflict();
jQ(document).ready(function()
{
	jQ('.login').click(function() { 
        jQ.blockUI({ message: jQ('#login') });
    });

    jQ('.close').click(function() { 
        jQ.unblockUI(); 
        return false; 
    });
});
