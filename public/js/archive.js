$(function () {
    
    $('.sign').click(function () {
        var btn = $(this);
        var sid = btn.attr('sid');
        
        $.blockUI();
        $.post('/journal/signshift/',{id:sid},
        function (data) {
            btn.toggleClass(data.ok ? 'btn-success btn-default disabled' : 'btn-danger', 200)
               .toggleClass(data.ok ? 'btn-success btn-primary' : 'btn-danger',500);
            btn.text(data.ok ? 'Подписано' : 'Подписать');
            $.unblockUI();
        }, 'json')
        .fail(function () {
            $.unblockUI();
            $.growlUI('Ошибка записи', 'Обновите страницу, и попробуйте снова', 5000);
        });
        
        return false;
    });
    
    // тоже самое что и выше, но с перезагрузкой страницы и без анимации 
    $('#btn-sign').click(function () {
        var btn = $(this);
        var sid = btn.attr('sid');
        
        $.blockUI();
        $.post('/journal/signshift/',{id:sid},
        function (data) {
            $.unblockUI();
            if (data.ok)
                location.reload();
            else
                $.growlUI('Ошибка подписи смены.', 'Обновите страцицу, и попробуйте еще раз.',5000);
        }, 'json')
        .fail(function () {
            $.unblockUI();
            $.growlUI('Ошибка подписи смены.', 'Обновите страцицу, и попробуйте еще раз.',5000);
        });
        
        return false;
    });
    
    // едалаем окно просмотра сообщений по высоте таким же как окно подписей (если оно есть)
    $('.panel-message').css({
        'min-height' : '404px'
    });
    
    //console.log($('.panel-signature').next().height());
    
});