<div class="modal-dialog">
    <div class="modal-content">
        @yield('start')

        <div class="modal-header">
            <h5 class="modal-title" id="modalLabel">{{ $metadata['title'] }}</h5>
            <button class="close" data-dismiss="modal" type="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            @yield('content')
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-dismiss="modal" type="button">Tutup</button>
            @yield('footer')
        </div>

        @yield('end')
    </div>
</div>
