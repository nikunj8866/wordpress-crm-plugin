var $j = jQuery.noConflict();


function upicrm_get_cookie(cname) {


    var name = cname + "=";


    var ca = document.cookie.split(';');


    for(var i=0; i<ca.length; i++) {


        var c = ca[i];


        while (c.charAt(0)==' ') c = c.substring(1);


        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);


    }


    return "";


}





function upicrm_set_cookie(cname, cvalue, exdays) {


    var d = new Date();


    d.setTime(d.getTime() + (exdays*24*60*60*1000));


    var expires = "expires="+d.toUTCString();


    document.cookie = cname + "=" + cvalue + "; " + expires;


}

jQuery(document).ready(function(){
    jQuery('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoHide: true
    });
})

