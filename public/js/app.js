$( document ).ready(function() {

    // Linking table rows
    $(".link-row").click(function() {
        window.location = $(this).data("href");
    });

});