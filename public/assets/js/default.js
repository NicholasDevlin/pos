window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

$.fn.select2.defaults.set('theme', 'bootstrap4');
$.fn.select2.defaults.set('placeholder', '');
$.fn.select2.defaults.set('allowClear', true);

const initializePlugins = () => {
    $(function() {
        $('.select2').select2();
        $('.select2-dynamic').select2({ tags: true });

        window['multiselect'] = $('.multiselect').multiselect({
            buttonWidth: '100%',
            enableCaseInsensitiveFiltering: true,
            enableFiltering: true,
            includeSelectAllOption: true,
            maxHeight: 400,
        });

        $('.thousand-separator').inputmask({
            alias: 'decimal',
            autoGroup: true,
            groupSeparator: ',',
            placeholder: '',
            removeMaskOnSubmit: true,
            rightAlign: false,
        });
    });
}

initializePlugins();

$(document).on('show.bs.modal', '#modal', function () {
    initializePlugins();
});

window.addEventListener('toastr', (e) => {
    const { type, message } = e.detail;
    toastr[type](message);
});
