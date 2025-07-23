/**
 * Created by bagba on 2018. 03. 24..
 */

$(document).ready(function(){
    var height = 2200;

    var docHeight = $(document).height();
    var pageNo = Math.ceil(docHeight / height);

    /**
        var div = $('.document-height');
        div.text(docHeight);
        div.css('color', 'blue')
    */
    var body = $('body');

    if ( docHeight > height ){
        body.css('height', (31.95 * pageNo).toString() + "cm");
    }else{
        body.css('height', "31.95cm");
    }

});