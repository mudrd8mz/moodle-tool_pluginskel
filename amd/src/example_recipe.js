define(['jquery'], function ($) {
    var init = function (recipes) {
        $('#id_example_options').change(function () {
            const val = $(this).val();
            $('#id_recipe').val(recipes[val]);
        });
    };

    return {
        init: init
    };
});
