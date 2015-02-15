var spcInfoPanel = function (container, message, isError) {
    $(container).find('.alert').addClass('hidden');

    var selector = '.alert-danger';
    if (isError === false) {
        selector = '.alert-success';
    }

    if (!message) {
        message = $(container).find(selector).data('description');
    }
    $(container).find(selector).html(message);
    $(container).find(selector).removeClass('hidden');
};

var spcNotifie = function (message, isError, timeout) {
    spcInfoPanel('.spc-panel-info', message, isError);
    if (timeout === true) {
        return;
    }
    setTimeout(function () {
        $('.spc-panel-info').find('.alert').addClass('hidden');
    }, timeout || 2000);
};

$(function () {
    $('body').on('click', '.spc-warning-link', function(e) {
        e.preventDefault();
        var $modal = $('.spc-warning-modal');

        $modal.find('.modal-title').empty().append($(this).data('message'));
        $modal.find('.modal-body').empty().append($(this).data('description'));
        $('.spc-warning-modal-link').attr('href', $(this).attr('href'));
        $modal.modal();
    });
});
