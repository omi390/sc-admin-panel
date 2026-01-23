"use strict"
$(window).on("load", () => {
    $("#landing-loader").fadeOut(1000);
    var img = $(".bg__img");
    img.css("background-image", function () {
        var bg = "url(" + $(this).data("img") + ")";
        var bg = `url(${$(this).data("img")})`;
        return bg;
    });
});

