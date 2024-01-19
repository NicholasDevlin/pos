$(document).on('ajax:success', function (_, xhr) {
    if (xhr.startsWith('<script>')) {
        $('body').append(xhr);
    } else {
        if (!$('#modal').length) {
            $('body').append('<div class="modal fade show" id="modal" tabindex="-1" aria-labelledby="modal"></div>');
        }

        $('#modal').html(xhr).modal('show');
    }
});
