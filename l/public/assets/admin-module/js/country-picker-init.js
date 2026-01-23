"use strict";
function initializePhoneInput(selector, outputSelector) {
    const phoneInput = document.querySelector(selector);
    const phoneNumber = phoneInput.value;
    const countryCodeMatch = phoneNumber.replace(/[^0-9]/g, "");
    const initialCountry = countryCodeMatch
        ? `+${countryCodeMatch}`
        : "bn".toLowerCase();

    let phoneInputInit = window.intlTelInput(phoneInput, {
        initialCountry: initialCountry.toLowerCase(),
        showSelectedDialCode: true,
    });
    if (!phoneInputInit.selectedCountryData.dialCode) {
        phoneInputInit.destroy();
        phoneInputInit = window.intlTelInput(phoneInput, {
            initialCountry: "bn".toLowerCase(),
            showSelectedDialCode: true,
        });
    }
    $(outputSelector).val(
        "+" +
            phoneInputInit.selectedCountryData.dialCode +
            phoneInput.value.replace(/[^0-9]/g, "")
    );

    $(".iti__country").on("click", function () {
        $(outputSelector).val(
            "+" +
                $(this).data("dial-code") +
                phoneInput.value.replace(/[^0-9]/g, "")
        );
    });

    $(selector).on("keyup keypress change", function () {
        $(outputSelector).val(
            "+" +
                phoneInputInit.selectedCountryData.dialCode +
                phoneInput.value.replace(/[^0-9]/g, "")
        );
        $(selector).val(phoneInput.value.replace(/[^0-9]/g, ""));
    });
}
$(document).ready(function () {
    try {
        initializePhoneInput(
            ".phone-input-with-country-picker",
            ".country-picker-phone-number"
        );
        initializePhoneInput(
            ".phone-input-with-country-picker2",
            ".country-picker-phone-number2"
        );
        initializePhoneInput(
            ".phone-input-with-country-picker3",
            ".country-picker-phone-number3"
        );
        initializePhoneInput(
            ".phone-input-with-country-picker4",
            ".country-picker-phone-number4"
        );
        initializePhoneInput(
            ".phone-input-with-country-picker5",
            ".country-picker-phone-number5"
        );
        initializePhoneInput(
            ".phone-input-with-country-picker6",
            ".country-picker-phone-number6"
        );
    } catch (error) {
        console.log(error)
    }
});
