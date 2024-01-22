@push('scripts')
    <script>
        const registerHot = new HandsontableWrapper('register', {
            columns: [
                { data: 'created_at_frmt', title: 'Waktu Transaksi', type: 'date', dateFormat: 'DD-MM-YYYY HH.mm.ss' },
                { data: 'subject_id', title: 'ID Pengguna' },
                { data: 'username', title: 'Penanggung Jawab' },
                { data: 'tag', title: 'Tag' },
            ],
        }).build();

        axios.get(`${window.location.href}?type=register`)
            .then(({ data }) => registerHot.loadData(data))
            .catch(() => toastr['error']('Data tidak berhasil ditampilkan!'));
    </script>
@endpush
