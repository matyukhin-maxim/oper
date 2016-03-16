idleTimer = null;
idleState = false;
idleWait = 15 * 60 * 1000;

$(function () {

    $(document).bind('mousemove keydown scroll', function () {
        clearTimeout(idleTimer); // отменяем прежний временной отрезок
        if (idleState === true) {
            // Действия на возвращение пользователя
            $("#overlay, #oinfo").fadeOut(1500, function () {
                location.reload();
            });
        }

        idleState = false;
        idleTimer = setTimeout(function () {
            // Действия на отсутствие пользователя
            $('select').blur(); // close all selectbox (for beauty)
            $('html, body').stop().animate({'scrollTop' : 0}, 500, 'swing', function () {
               $("#overlay, #oinfo").fadeIn(3000);
               idleState = true;
            });
        }, idleWait);
    });

    $("body").trigger("mousemove"); // сгенерируем ложное событие, для запуска скрипта
});
