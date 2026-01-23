"use strict"
$(document).ready(function () {
    $('.zone-select').select2({
        placeholder: "Select Zone"
    });
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

    if (lang == '{{$default_lang}}') {
        $(".from_part_2").removeClass('d-none');
    } else {
        $(".from_part_2").addClass('d-none');
    }
});
