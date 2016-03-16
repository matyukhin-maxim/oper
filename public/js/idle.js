$(function () {
    function mupdate() {
        $.ajax({
            url: "/dashboard/reloadmessages/",
            type: 'POST',
            success: function (data) {
                $('#msg-body').html(data);
            }
        });
    }
    window.setInterval(function () {
        mupdate();
    }, 60000);
});