$(function () {
    
    $('.disabled').click(function () {
        return false;
    });

    $('.modal').on('hide.bs.modal', function (e) {
        $(this).removeData('bs.modal');
    });
    
    $('.modal').on('loaded.bs.modal', function () {
        $('#shift-date').datepicker();
    });
    
    $('#tpicker').timepicker();
    
    $('.back-link').click(function (e) {
        e.preventDefault();
        if (history.length > 1)
            history.back(1);
        else
            location.href = '/journal/';
    }); 

    // auto hide all dissmissable alerts (setTimeout - once)
    window.setInterval(function () {
        $('.alert-dismissable').each(function (idx, adiv) {
            adiv = $(this);

            setTimeout(function () {
                adiv.slideUp(1000, function () {
                    adiv.remove();
                });
            }, 900 * idx);
        });
    }, 10000);
    
    //({
    //    message: '<h4><i class="glyphicon glyphicon-time"></i> Обработка...</h4>'
    //})
    
    //$(document).ajaxStart($.blockUI).ajaxStop($.unblockUI);
    
});
