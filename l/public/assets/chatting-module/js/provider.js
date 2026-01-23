"use Strict"
$('#user_type').on('change', function () {
    if(this.value==='super-admin'){
        $('#super-admin').show();
        $('#serviceman').hide();
    }else if(this.value==='provider-serviceman'){
        $('#super-admin').hide();
        $('#serviceman').show();
    }
});

$("#chat-search").on("keyup", function () {
    var value = this.value.toLowerCase().trim();
    $(".inbox_chat > div").show().filter(function () {
        return $(this).text().toLowerCase().trim().indexOf(value) == -1;
    }).hide();
});
