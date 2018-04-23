$( document ).ready(function() {

    console.log('test');
    // Linking table rows
    $(".link-row").click(function() {
        window.location = $(this).data("href");
    });

});