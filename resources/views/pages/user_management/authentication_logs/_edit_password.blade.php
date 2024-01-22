@push('scripts')
    <script>
        const editPasswordHot = new HandsontableWrapper('edit-password', {
            columns: [
                { data: 'created_at_frmt', title: 'Waktu Transaksi', type: 'date', dateFormat: 'DD-MM-YYYY HH.mm.ss' },
                { data: 'subject_id', title: 'ID Pengguna' },
                { data: 'username', title: 'Penanggung Jawab' },
                { data: 'tag', title: 'Tag' },
            ],
        }).build();

        axios.get(`${window.location.href}?type=edit-password`)
            .then(({ data }) => editPasswordHot.loadData(data))
            .catch(() => toastr['error']('Data tidak berhasil ditampilkan!'));
    </script>
@endpush
