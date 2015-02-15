var spcForm = {
    startSending: function()
    {
        spcForm.hideErrors();
        spcLoader.show();
    },

    showErrors: function(errors)
    {
        spcForm.hideErrors();
        for (var form in errors) {
            for (var field in errors[form]) {
                var errorStr = '<div class="errorMessage">';
                for (var i = 0; i < errors[form][field].length; i++) {
                    errorStr += errors[form][field][i];
                    if (i < errors[form][field].length - 1) {
                        errorStr += '<br />';
                    }
                }
                errorStr += '</div>';
                var selector = "[name='" + form + "[" + field +"]']";
                $(selector).closest('.form-group').addClass('has-error');
                if ($(selector).hasClass('spc-inside-input-group')) {
                    $(selector).closest('.input-group').after(errorStr);
                } else {
                    $(selector).after(errorStr);
                }
            }
        }
    },

    hideErrors: function()
    {
        $('.has-error').removeClass('has-error');
        $('.errorMessage').remove();
    }
};