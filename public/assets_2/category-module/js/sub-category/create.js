"use strict"
$(document).ready(function () {
    $('.js-select').select2();
});

$(".lang_link").on('click', function (e) {
    e.preventDefault();
    $(".lang_link").removeClass('active');
    $(".lang-form").addClass('d-none');
    $(this).addClass('active');

    let form_id = this.id;
    let lang = form_id.substring(0, form_id.length - 5);
    console.log(lang);
    $("#" + lang + "-form").removeClass('d-none');
});
