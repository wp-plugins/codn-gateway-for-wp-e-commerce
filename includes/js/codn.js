var $Url="http://app.codnusantara.com/payment";
jQuery(function($){
        var object=$('.codn_button');
    $("body").append('<div style="display: none;margin:auto;" id="codn_panel"><div id="close"><img src="'+WPURLS.imgurl+'"></div><iframe id="codn_iframe"></iframe></div>');
    $('.codn_button').click(function(event) {
        var url=$Url+'?&d='+encodeURIComponent(object.attr("codn_data"))+"&s="+encodeURIComponent(object.attr("codn_signature"));
        $("#codn_panel").css("display","block");
        $("#codn_iframe").attr("src",url);
    });
    $('#close').click(function(event) {
        $("#codn_panel").css("display","none");
    });
})