$(document).on('ajax:success', function (_, xhr) {
    if (xhr.startsWith('<script>')) {
        $('body').append(xhr);
    } else if (xhr.startsWith('<div id="print">')) {
        const iframeWindow = document.getElementById('print-iframe').contentWindow;
        iframeWindow.document.open();
        iframeWindow.document.write(xhr);
        iframeWindow.document.close();
        iframeWindow.print();
        window.location.reload();

        // var myWindow=window.open('','');
        // myWindow.document.write(xhr);
        // myWindow.document.close();

        // myWindow.focus();
        // myWindow.print();
        // myWindow.close();
    } else {
        if (!$('#modal').length) {
            $('body').append('<div class="modal fade show" id="modal" tabindex="-1" aria-labelledby="modal"></div>');
        }

        $('#modal').html(xhr)
            .modal({ backdrop: 'static', keyboard: false })
            .modal('show');
    }
});
