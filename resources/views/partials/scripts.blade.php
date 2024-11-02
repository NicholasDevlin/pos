<script src="{{ asset('drezoc/js/jquery.min.js') }}"></script>
<script src="{{ asset('drezoc/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('drezoc/js/metismenu.min.js') }}"></script>
<script src="{{ asset('drezoc/js/simplebar.min.js') }}"></script>
<script src="{{ asset('drezoc/js/waves.js') }}"></script>
<script src="{{ asset('assets/js/theme.js') }}"></script>

<script src="{{ asset('assets/plugins/handsontable/dist/handsontable.full.min.js') }}"></script>
<script src="{{ asset('assets/js/handsontable.js') }}"></script>
<script src="{{ asset('assets/plugins/axios/dist/axios.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery-ujs/src/rails.js') }}"></script>
<script src="{{ asset('assets/js/ujs-modal.js') }}"></script>
<script src="{{ asset('assets/js/shiftclick-multicheckboxes.js') }}"></script>

<script src="{{ asset('assets/plugins/bootstrap-multiselect/js/bootstrap-multiselect.min.js') }}"></script>
<script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('assets/plugins/Inputmask/jquery.inputmask.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>

<script src="{{ asset('assets/plugins/toastr/build/toastr.min.js') }}"></script>
<script defer src="{{ asset('assets/plugins/alpine/packages/mask/dist/cdn.min.js') }}"></script>
<script src="{{ asset('assets/plugins/table-to-excel-by-linways/dist/tableToExcel.js') }}"></script>
<script src="{{ asset('assets/plugins/xlsx-js-style/dist/xlsx.bundle.js') }}"></script>

<script>
    @session('success')
    toastr['success']("{{ $value }}");
    @endsession

    @session('fail')
    toastr['error']("{{ $value }}");
    @endsession
</script>

<script src="{{ asset('assets/js/default.js') }}"></script>
