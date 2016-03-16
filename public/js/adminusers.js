$(function () {
    /*
     $('#usertable').dataTable({
     dom           : '<"panel-heading"f><"panel-body"rt><"panel-footer"pi>',
     processing    : true,
     serverSide    : true,
     ajax          : {
     url : "/admin/json/",
     type: "POST"
     }
     });
     */

    function filter(text, page) {
        $('.usertable').block({message: 'Загрузка...'});
        $.post('/admin/json/', {
            limit: 20,
            page : page,
            text : text
        }, function (data) {
            //console.log(data);
            $('#usertable > tbody').html(data);
            $('.usertable').unblock();
        });
    }
    
    var tmr;
    $('#ufilter').keyup(function (e) {
        //console.log(e);
        clearTimeout(tmr);
        tmr = setTimeout(function() {
            filter( $('#ufilter').val(), 1);
        }, 500);
    });
    
    filter('',1);
});