/**
 * Created by Матюхин_МП on 28.04.2016.
 */

$(function () {

    $('.btn-check').click(function(e) {
        e.preventDefault();

        var btn = $(this);
        var chk = 1 - btn.data('check');
        btn.data('check', chk).blur();
        btn.find('i').toggleClass('glyphicon-ok').html(chk ? '' : '&nbsp;');

        $('.btn-copy').prop('disabled', $('.glyphicon-ok').length === 0);
    });

    $('.copy-messages').click(function (e) {
        e.preventDefault();

        // пробегаем по всем кнопкам, и если она "чекнута" то возвращаем id сообщения
        // и эмудируем клик, чтобы снять галку
        var ids = $('.btn-check').map(function (id, item) {
            var btn = $(item);
            return btn.data('check') ? btn.trigger('click').data('id') : null;
        }).toArray();

        console.info(ids);
        // шлем запрос серверу, который все это дело обработет
        $.ajax({
            type: 'POST',
            url : '/journal/copyMessages/',
            data: {list: ids, dest: $(e.target).data('journal')},
            success: function (resp) {
                $('#row-error').html(resp);
                $('html').animate({scrollTop: 0}, "slow");
            }
        });
    });

    $('.btn-copy').prop('disabled', 1);
});