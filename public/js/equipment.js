$(function () {
    
    $('#btn-equip').click(function () {
        $.post('/journal/equipmentsave/',
        $('textarea').serialize(),
        function (data) {
            $('#row-error').html(data);
            $('html').animate({scrollTop: 0}, "slow");
        });
        return false;
    });
    
    $('.tab-title').on('shown.bs.tab', function () {
        var href = $(this).attr('href');
        var area = $(href).find('textarea');
        text = area.val();
        area.focus().val('').val(text); // move caret at and
    });
    
});