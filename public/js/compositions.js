$(function () {
    
    // переносим классы ячеек таблицы на селекторы,
    // ибо на селекторах и их классах построена логика работы
    // добавления/удаления стажеров
     $('td > select').each(function () {
        var select = $(this);
        var row = select.closest('tr');
        var rid = row.attr('ruleid') || 0;
        select.addClass(select.parent().attr('class'));
        select.parent().attr('class',''); // у самих ячек классы убираем, чтобы события не срабатывали
        if (select.hasClass('standin')) select.find('option[value=""]').text('Добавить стажера...');
        if (select.hasClass('trainee')) select.find('option[value=""]').text('Добавить дублера...');
        
        // делаем неактивными селекты в disabled строках
        // и присваем имя переменной для отправки у тех селектов, которые активны
        if (select.hasClass('mainuser')) {
            var name = row.hasClass('disabled') ? '' : 'users[' + rid + ']';
            select.attr('disabled',row.hasClass('disabled'));
            if (name) select.attr('name',name);
        }
    });
    
    // прячем селекты стажера/дублера в неактивных строках
    $('tr.disabled > td  > select.selector').hide();
    //$('select.mainuser:enabled').attr('name','user')
    
    $('.selector').change(function () {
        // добавление стажера/дублера по шаблону
        // имена гинерируем динамически, и передавать будем в  виде массива
        var select = $(this);
        var item = select.find('option:selected');
        var rid = select.closest('tr').attr('ruleid') || 0;
        var readonly = select.closest('tr').hasClass('disabled') ? '-view' : '';
        if (item.val()) {
            var tmpl = $('#template' + readonly).clone().toggleClass('clone hidden').removeAttr('id');
            tmpl.find('input[type="text"]').val(item.text());
            tmpl.find('input[type="hidden"]')
                    .attr('name', select.hasClass('standin') ? 'standin[' + rid + '][]' : 'trainee[' + rid + '][]')
                    .val(item.val());
            tmpl.appendTo(select.parent());
        }
        select.val('');
    });
    
    $(document).on('click','.xremove', function () {
        
        // удаляем стажера/дублера при клике по кнопке X
        // и поставив фокус на ближайший селектор
        $(this).closest('td').find('.selector').focus();
        $(this).closest('div').remove();

        return false;
    });
    
    $('.save-compositions').click(function () {
        $.post('', $('#comp-form').serialize(),
        function(data) {
            $('html, body').stop().animate({'scrollTop' : 0}, 500, 'swing', function () {
                //location.reload();
                $('#row-error').html(data);
            });
        });
        return false;
    });
    
    // смена сотрудника у должности
    $('.mainuser').change(function () {
        var row = $(this).closest('tr');
        // удаляем всех клонов (стажеры/дублеры) в текущей строке таблицы
        row.find('.clone').remove();                
        // и делаем недоступными, если значение пользователя не указано
        row.find('.selector').attr('disabled',!$(this).val());     
        //row.toggleClass('success',$(this).val() !== '');
    });
    
    $.get('',{},
    function(data) {
        //console.log(data);
        $.each(data, function (idx, item) {
            var row = $('tr[ruleid="' + idx + '"]');
            row.find('select.mainuser').val(item.user).trigger('change');
            
            var slist = item.standin || [];
            var tlist = item.trainee || [];
            
            // стажеры
            for (var idx in slist) {
                row.find('.standin').val(slist[idx]).trigger('change');
            }
            
            // дублеры
            for (var idx in tlist) {
                row.find('.trainee').val(tlist[idx]).trigger('change');
            }
        });
        //$('.mainuser:enabled').trigger('change');
        
        
        //$('tr[ruleid="1"]').find('.standin').val('23').trigger('change');
        //$('tr[ruleid="115"]').find('.trainee').val('326').trigger('change');
        //$('tr[ruleid="5"]').find('.trainee').val('326').trigger('change');
    },'json');
    
    $('.mainuser:enabled').trigger('change');
    
    /**
     * @todo Сделать контроль изменений любого з полей, 
     * и запрещать уходить со старницы пока не будет 
     * изменения не будут сохранены
     */

    
});