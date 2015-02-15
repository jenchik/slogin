var spcLoader = {
    show: function(spinner)
    {
        if (spinner === true && typeof(spinnerShow) == 'function') {
            spinnerShow(10000);
            return;
        }
        $('body').prepend('<div class="spc-loader-overlay" style="position:fixed; left:0; top:0; z-index: 10000; background: rgba(0,0,0,0.35); width: 100%; height: 100%;" />');
        $('.spc-loader-wrapper').css({
            position: 'fixed',
            top: ($(window).height() / 2) - ($('.spc-loader-wrapper').outerHeight()) + 'px',
            left: ($(window).width() / 2) - ($('.spc-loader-wrapper').outerWidth() / 2) + 'px',
            'z-index': 10001
        });
        $('.spc-loader-wrapper').removeClass('spc-dontshow');
    },
    hide: function()
    {
        if (typeof(spinnerHide) == 'function') {
            spinnerHide();
            //return;
        }
        $('.spc-loader-overlay').remove();
        $('.spc-loader-wrapper').addClass('spc-dontshow');
    }
};