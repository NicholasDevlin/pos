window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

$.fn.select2.defaults.set('theme', 'bootstrap4');
$.fn.select2.defaults.set('placeholder', '');
$.fn.select2.defaults.set('allowClear', true);
$('.select2').select2();

$('.multiselect').multiselect({
    buttonWidth: '100%',
    enableCaseInsensitiveFiltering: true,
    enableFiltering: true,
    includeSelectAllOption: true,
});

$('.thousand-separator').inputmask({
    alias: 'decimal',
    autoGroup: true,
    groupSeparator: ',',
    placeholder: '',
    removeMaskOnSubmit: true,
    rightAlign: false,
});
