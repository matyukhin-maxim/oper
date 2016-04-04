$(function () {
    
    $('.disabled').click(function (e) {
        e.preventDefault();
        return false;
    });

    $('.modal').on('hide.bs.modal', function (e) {
        $(this).removeData('bs.modal');
    });
    
    $('.modal').on('loaded.bs.modal', function () {
        $('#shift-date').datepicker();
        $('.datepicker').datepicker();

        // calc shift watch
        $('#shift-date, #iselector').change(function (e) {
            e.preventDefault();
            $.post('/journal/calc/', $('input, select').serialize(),
            function(data) {
                $('#wselector').val(data.sw || 1);
                //$('#iselector').val(data.sinterval || 1);
            }, 'json');
        }).filter(':first').trigger('change');
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

    $('.datepicker').datepicker();


    /*
    var panel = $('.panel-message');
    if (panel.length) {
        var div = panel[0];
        var offset = div.scrollHeight - parseInt($(div).css('max-height'));
        if (offset) $('.panel-heading').css('padding-right', '35px');
        console.info(offset);
    }
    */
});
