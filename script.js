function codeShadow() {

    $.ajax({
        url: '/handle',
        beforeSend: function() {
            $('.first-block__content__analis-button').attr('value', 'Загрузка');
        },
        success: function (req) {
            setTimeout(function () {
                $('#second-block__content').html(req);

                document.getElementById('block-shadow').scrollIntoView();

                document.getElementById("block-shadow").style.padding = "80px 0 0 0";
                document.getElementById("block-shadow").style.minHeight = "100vh";
                document.getElementById("block-shadow").style.fontSize = "30px";
                document.getElementById("roro").style.margin = "15px 0px";
                document.getElementById("itemes").style.border = "3px solid black";
                document.getElementById("item").style.border = "3px solid black";
            }, 2000);
        }
    });


}
