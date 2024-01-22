window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

$.fn.select2.defaults.set('placeholder', '');
$.fn.select2.defaults.set('allowClear', true);

$('.multiselect').multiselect({
    buttonWidth: '100%',
    enableCaseInsensitiveFiltering: true,
    enableFiltering: true,
    includeSelectAllOption: true,
});

$('.thousand_separator').inputmask({
    alias: 'decimal',
    autoGroup: true,
    groupSeparator: ',',
    placeholder: '',
    removeMaskOnSubmit: true,
    rightAlign: false,
});
