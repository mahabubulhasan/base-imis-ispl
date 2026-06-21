<script type="text/javascript">

       toastr.options = {
        "closeButton": true,
        "timeOut": "0",
        "extendedTimeOut": "0"
    };
    document.addEventListener('click', function() {
    setTimeout(() => {
        toastr.clear(); 
    }, 10000);
}, { once: true });

       @if ($message = Session::get('success'))      
        toastr.success('{{ $message }}')
       @endif
       @if ($message = Session::get('error'))      
        toastr.error('{{ $message }}')
       @endif
       @if ($message = Session::get('warning'))
        toastr.warning('{{ $message }}')
       @endif
       @if ($message = Session::get('info'))
        toastr.info('{{ $message }}')
       @endif

</script>