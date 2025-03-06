{{-- blade-formatter-disable --}}
@push('scripts')
    <script>
        const loginHot = new HandsontableWrapper({
            tableId: 'login',
            title: 'Log Pengguna (Login)',
        }, {
            columns: [
                { data: 'created_at', title: 'Waktu Transaksi', type: 'date', dateFormat: 'DD-MM-YYYY HH.mm.ss' },
                { data: 'username', title: 'Username' },
                { data: 'tag', title: 'Tag' },
                { data: 'event_frmt', title: 'Jenis', renderer: 'html' },
                { data: 'properties_frmt', title: 'Keterangan', renderer: 'html', multiColumnSorting: { headerAction: false } },
            ],
        }).build();

        axios.get(`${window.location.href}?type=login`)
            .then(({ data }) => loginHot.loadData(data))
            .catch(() => toastr['error']('Data tidak berhasil ditampilkan!'));
    </script>
@endpush
