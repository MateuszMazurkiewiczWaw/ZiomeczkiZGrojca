$(document).ready(function () {

    //kliknij na signup, schowaj login, pokaz rejestracje
    $("#signup").click(function () {
        $("#first").slideUp("slow", function () {
            $("#second").slideDown("slow");
        })
    });

    //schowaj rejestracje, pokaz login
    $("#signin").click(function () {
        $("#second").slideUp("slow", function () {
            $("#first").slideDown("slow");
        })
    });
});