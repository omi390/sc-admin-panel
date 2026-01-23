"use strict";

document.addEventListener('DOMContentLoaded', function () {
    var telInputs = document.querySelectorAll('input[type="tel"]');

    telInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            formatPhoneNumber(this);
        });
    });

    function formatPhoneNumber(input) {
        input.value = input.value.replace(/[^+\d]+$/g, '').replace(/(\..*)\./g, '$1');
    }
});
