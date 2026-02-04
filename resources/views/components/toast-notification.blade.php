@push('scripts')
    <script>
        $(function () {
            // General Toastr Options
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-bottom-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Handle session flash messages
            @if (session('success'))
                toastr['success']("{{ session('success') }}");
            @endif

            @if (session('error'))
                toastr['error']("{{ session('error') }}");
            @endif

            @if (session('warning'))
                toastr['warning']("{{ session('warning') }}");
            @endif

            @if (session('info'))
                toastr['info']("{{ session('info') }}");
            @endif

            // Handle validation errors
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr['error']("{{ $error }}");
                @endforeach
            @endif

            // Handle manual triggers via buttons (.btn-toastr)
            $(document).on('click', '.btn-toastr', function () {
                let context = $(this).data('context') || 'info';
                let message = $(this).data('message') || '';
                let position = $(this).data('position') || 'top-right';

                toastr.options.positionClass = 'toast-' + position;
                toastr[context](message);
            });
        });
    </script>
@endpush