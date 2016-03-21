$(function () {
    
    //$(document).ajaxStart($.blockUI).ajaxStop($.unblockUI);
    
    // удаление сообщения
    $('.msg-action-delete').click(function () {
        var cell = $(this);
        var link = cell.attr('href');
        if (confirm('Удалить выбранное сообщение?') === true) {
            $.post(link,[],function (data) {
                if (data.ok) cell.closest('tr').remove();
            },'json');
        }
        return false;
    });
    
    $('#close-shift-link').click(function () {
        if (confirm('Вы действительно хотите сдать текущую смену?') === true) {
            $.post('/journal/closeshift/',
            {agree: $('#tpicker').val()}, 
            function (data) {
                location.reload();
                //console.log(data);
            });
        }
        return false;
    });

    // отправка на сервер нового текста сообщения
    $('.message-content').change(function () {
        var row = $(this).closest('tr');
        var mid = $(this).attr('mid');

        $.post('/journal/editmessage/',
        {
            mid: mid,
            message: this.value
        },
        function(data) {
            row.toggleClass('special', data.mark || false);
            row.find('.status').fadeOut(400);
        }, 'json');
    });

    // устанавливам датапикеры, и привязываем событие при смене даты
    $('.datetimepicker').each(function () {
        var mid = $(this).attr('mid');
        var tid = "#t" + mid;
        //var row = $(this).closest('tr');
        $(this).datetimepicker({
            minDate: -5,
            maxDate: 15,
            altField: tid,
            //oneLine: true,
            onClose: function (pdate) {
                $.post('/journal/changemessagetime/',
                {
                    mid: mid,
                    mdate: pdate,
                    mtime: $(tid).val()
                },
                function (data) {
                    if (data.ok) location.reload();
                },'json');
            }
        });
    });
    
    // обработчик добавлния сообщения
    $('#btn-new-message').click(function () {
        var message = $('#new-message-text');
        var text    = message.val();
        message.toggleClass('has-error', text.length === 0);
        
        if (message.hasClass('has-error')) return false;
        $.post('/journal/newmessage/', {
            message: text,
            mdate: $('#new-message-time').val()
        },
        function (data) {
            $('html, body').animate({scrollTop: 0}, "slow", function() {
                if (data.ok) location.reload(); 
            });
        },'json');
        
        return false;
    });
        
    var pnl = $('.panel-message');
    if (pnl.length) pnl.scrollTop(pnl[0].scrollHeight);
    //pnl.stop().animate({scrollTop:pnl[0].scrollHeight}, 2000, 'swing');
    
    var timeout;
    $('.message-content').bind('keydown', function () {
        var ctrl = $(this);
        var cell = ctrl.parent();
        cell.find('.status').fadeIn(600);
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            cell.find('.status').fadeOut(600);
            ctrl.trigger('change');
        }, 3000);
    });
    
    $('#new-message-text').focus();
    $('#new-message-time').datetimepicker();    
});