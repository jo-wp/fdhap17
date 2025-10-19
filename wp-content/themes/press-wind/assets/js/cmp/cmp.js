jQuery(function ($) {
    $('#open-modal').on('click', function (e) {
        e.preventDefault();
        if (window.Sddan && window.Sddan.cmp && typeof window.Sddan.cmp.displayUI === 'function') {
            window.Sddan.cmp.displayUI();
        } else {
            console.error('Sddan CMP is not loaded.');
        }
    });
});