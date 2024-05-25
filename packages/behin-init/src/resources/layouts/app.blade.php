
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ url('public/dashboard/plugins/font-awesome/css/font-awesome.min.css')  . '?' . config('app.version') }}">
    <!-- Ionicons -->
    {{-- <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css"> --}}
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ url('public/dashboard/dist/css/adminlte.min.css')  . '?' . config('app.version') }}">
    <!-- Date Picker -->
    <link rel="stylesheet" href="{{ url('public/dashboard/plugins/datepicker/datepicker3.css')  . '?' . config('app.version') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ url('public/dashboard/plugins/daterangepicker/daterangepicker-bs3.css')  . '?' . config('app.version') }}">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="{{ url('public/dashboard/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css')  . '?' . config('app.version') }}">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <!-- bootstrap rtl -->
    <link rel="stylesheet" href="{{ url('public/dashboard/dist/css/bootstrap-rtl.min.css')  . '?' . config('app.version') }}">
    <!-- template rtl version -->
    <link rel="stylesheet" href="{{ url('public/dashboard/dist/css/custom-style.css')  . '?' . config('app.version') }}">
    <link rel="stylesheet" href="{{ url('public/dashboard/dist/css/custom.css')  . '?' . config('app.version') }}">

    {{-- <link rel="stylesheet" href="{{ url('public/dashboard/dist/css/custom.css')  . '?' . config('app.version') }}"> --}}
    <link rel="stylesheet" type="text/css" href="{{ url('public/dashboard/plugins/datatables/dataTables.bootstrap4.css')  . '?' . config('app.version') }}" />
    <link rel="stylesheet" href="{{ url('public/dashboard/dist/css/dropzone.min.css')  . '?' . config('app.version') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="{{ Url('public/dist/css/persian-datepicker-0.4.5.min.css')  . '?' . config('app.version') }}" />
    <link rel="stylesheet" href="{{ Url('public/green-player/css/green-audio-player.css')  . '?' . config('app.version') }}" />
    <link rel="stylesheet" href="{{ Url('public/green-player/css/green-audio-player.min.css')  . '?' . config('app.version') }}" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('public/dashboard/plugins/select2/select2.min.css') }}">
    @yield('style')

    <script src="{{ url('public/dashboard/plugins/jquery/jquery.min.js') . '?' . config('app.version') }}"></script>
    <script src="{{ url('public/dashboard/plugins/datatables/jquery.dataTables.js')  . '?' . config('app.version') }}"></script>
    <script src="{{ url('public/dashboard/plugins/datatables/dataTables.bootstrap4.js')  . '?' . config('app.version') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="{{ url('public/js/ajax.js')  . '?' . config('app.version') }}"></script>
    <script src="{{ url('public/js/dataTable.js')  . '?' . config('app.version') }}"></script>
    <script src="{{ url('public/js/dropzone.js')  . '?' . config('app.version') }}"></script>
    @yield('script_in_head')
    
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
    
        @include('layouts.header')
    
        @include('layouts.main-sidebar')
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>



        <footer class="main-footer">
            {{-- <strong> &copy; 2018 <a href="http://github.com/hesammousavi/">حسام موسوی</a>.</strong> --}}
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
    </div>

        <!-- jQuery UI 1.11.4 -->
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
        <script>
        $.widget.bridge('uibutton', $.ui.button)
        </script>
        <!-- Bootstrap 4 -->
        <script src="{{ url('public/dashboard/plugins/bootstrap/js/bootstrap.bundle.min.js')  . '?' . config('app.version') }}"></script>
        <!-- Morris.js charts -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
        <!-- Sparkline -->
        <!-- jvectormap -->
        <!-- jQuery Knob Chart -->
        <script src="{{ url('public/dashboard/plugins/knob/jquery.knob.js')  . '?' . config('app.version') }}"></script>
        <!-- daterangepicker -->
        <script src="{{ url('public/dashboard/plugins/daterangepicker/daterangepicker.js')  . '?' . config('app.version') }}"></script>
        <!-- datepicker -->
        <script src="{{ url('public/dashboard/plugins/datepicker/bootstrap-datepicker.js')  . '?' . config('app.version') }}"></script>
        <!-- Bootstrap WYSIHTML5 -->
        <script src="{{ url('public/dashboard/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')  . '?' . config('app.version') }}"></script>
        <!-- Slimscroll -->
        <!-- FastClick -->
        <!-- AdminLTE App -->
        <script src="{{ url('public/dashboard/dist/js/adminlte.js')  . '?' . config('app.version') }}"></script>

        <script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js"></script>
        <script src="{{ Url('public/dist/js/persian-date-0.1.8.min.js')  . '?' . config('app.version') }}"></script>
        <script src="{{ Url('public/dist/js/persian-datepicker-0.4.5.min.js')  . '?' . config('app.version') }}"></script>

        <script src="{{ url('public/dashboard/plugins/select2/select2.full.min.js')}}"></script>
        <!-- include FilePond library -->
        <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>

        <!-- include FilePond plugins -->
        <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>

        <!-- include FilePond jQuery adapter -->
        <script src="https://unpkg.com/jquery-filepond/filepond.jquery.js"></script>
        
        
        
        <script>

            // $(document).ready(function() {
            //     $('#table').DataTable({
            //         dom: 'Bfltip',
            //         buttons: [{
            //             'extend': 'excel',
            //             'text': 'Excel',
            //             className: 'btn btn-info'
            //         }],
            //         'ajax': ''
            //     });
            //     $('table').attr('style', 'font-weight:bold; font-size: 12px')

            //     initial_view();
            //     hide_loading();
            // });

            function initial_view(){
                $('.select2').select2();
                $('.select2').css('width', '100%')
                $(".persian-date").persianDatepicker({
                    viewMode: 'year',
                    format: 'YYYY-MM-DD',
                    initialValueType: 'persian'
                });
            }

            

            

            

        </script>

        <script>
            // $('form').keypress(function(event) {
            //     if (event.keyCode == 13) {
            //         event.preventDefault();
            //     }
            // });
            

        </script>
        <script src="{{ url('public/js/loader.js')  . '?' . config('app.version') }}"></script>
        <script src="{{ url('public/js/clearcach.js')  . '?' . config('app.version') }}"></script>
        <script src="{{ url('public/js/scripts.js')  . '?' . config('app.version') }}"></script>

        @yield('script')
    </div>



</body>

</html>
