$(function () {

    $(document).ajaxStart($.blockUI).ajaxStop($.unblockUI);

    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: '/auth/getlist/',
        success: function (data) {
            $('#autologin').autocomplete({
                autoFocus: true,
                source: data,
                select: function (ev, ui) {
                    $('#userid').val(ui.item.data);
                    $(this).val(ui.item.label);
                    $('#upass').focus();
                    return false;
                },
                response: function (ev, ui) {
                    $(this).parent().toggleClass('has-error', ui.content.length === 0);
                    // автозавершение ввода, если в списке "живого поиска" остался только один вариант
                    if (ui.content.length === 1) {
                        $(this).val(ui.content[0].label);
                        $('#userid').val(ui.content[0].data);
                        //$(this).autocomplete( "close" );
                        $('#upass').focus();
                    }
                }
            });
        }
    });
    
    $('.form-control').val('');

});