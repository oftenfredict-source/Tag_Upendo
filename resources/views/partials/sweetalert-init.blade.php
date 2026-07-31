{{-- SweetAlert2: session flash + global confirm handler --}}
@if(session('success') || session('error') || session('new_member_accounts'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-session-flash], .app-content > .alert-success, .app-content > .alert-danger, .app-content > .alert-info').forEach(function (el) {
        el.remove();
    });

    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: @json(__('Success')),
        html: {!! json_encode(session('success')) !!},
        confirmButtonColor: '#940000'
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: @json(__('Error')),
        text: @json(session('error')),
        confirmButtonColor: '#940000'
    });
    @endif

    @if(session('new_member_accounts'))
    Swal.fire({
        icon: 'info',
        title: @json(__('Member login credentials')),
        html: @json(view('partials.member-credentials-table', ['accounts' => session('new_member_accounts')])->render()),
        confirmButtonColor: '#940000',
        width: 600
    });
    @endif
});
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-swal-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.swalConfirmed === '1') {
                return;
            }
            e.preventDefault();
            Swal.fire({
                title: form.dataset.swalTitle || @json(__('Confirm')),
                text: form.dataset.swalConfirm || @json(__('Are you sure?')),
                icon: form.dataset.swalIcon || 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: form.dataset.swalConfirmText || @json(__('Yes')),
                cancelButtonText: @json(__('Cancel'))
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.dataset.swalConfirmed = '1';
                    form.submit();
                }
            });
        });
    });

    window.swalConfirm = function (options) {
        return Swal.fire(Object.assign({
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: @json(__('Yes')),
            cancelButtonText: @json(__('Cancel'))
        }, options));
    };
});
</script>
