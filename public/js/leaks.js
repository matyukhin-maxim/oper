/**
 * Created by Матюхин_МП on 06.04.2016.
 */
$(function () {

    function recalc() {

        var time      = parseFloat($('#calctime').val()),
            pr_start  = parseFloat($('#b-pressure').val()),
            pr_stop   = parseFloat($('#e-pressure').val()),
            gas_start = parseFloat($('#b-gas').val()),
            gas_stop  = parseFloat($('#e-gas').val()),

            m_max     = parseFloat($('#pmax').val()),
            m_limit   = parseFloat($('#plimit').val())
            ;

        var res = (1-(((m_max/m_limit)*pr_stop+0.938)*(gas_start+273))/(((m_max/m_limit)*pr_start+0.938)*(gas_stop+273)))
                - (24/time);

        $('#result').val(isNaN(res) ? '-' : res.toFixed(2) + '%');
        $('#btn-save').prop('disabled', isNaN(res));

        $('.hidden').val(res / 100);    // чтобы потом не отрезать %, сохраним не форматированные данные
        return false;
    }

    $('#date').val(moment().format('DD.MM.YYYY'));
    $('#result').addClass('strong').prop('readonly', true).val('-');

    $('.view').click(function (e) {
        e.preventDefault();

        var btn = $(this);
        $('.view').removeClass('active');
        btn.addClass('active');

        $.get('data/id/' + btn.data('block'), {},
        function(data) {
            $('#archive').html(data);
            $('.panel-message').stop().animate({scrollTop: 0}, 'slow');
        });
    }).filter(':first').trigger('click');

    $('input').change(function () {
        recalc();
    });

    $('#btn-calc').click(function () {
        recalc();
    });

    $('#btn-save').click(function () {

        $.post('save/', $('[type="text"]').serialize(),
        function(data) {
            //$('#archive').html(data);
            if (data === 'ok') $('.view[data-block="' + $('#block').val() + '"]').trigger('click');
        });
    });

    recalc();
});