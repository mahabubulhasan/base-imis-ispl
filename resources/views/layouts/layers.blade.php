<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<!DOCTYPE html>
<html lang="en" style="height: auto;">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{csrf_token()}}">
        <title>@yield('title') &mdash; IMIS Dashboard</title>

        <!-- Theme style -->
        <link rel="stylesheet" href="{{asset('css/app.css')}}">
        <link rel="stylesheet" href="{{asset('css/style.css')}}">
        <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css">
    </head>

<body class="sidebar-mini sidebar-collapse layout-fixed layout-navbar-fixed layout-footer-fixed" style="height: auto;">

        <div class="wrapper">       
            <!-- Header -->
            @include('includes.header')
            <!-- Sidebar -->
            @include('includes.sidebar')
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                    <section class="content-header">
                        <div class="container-fluid">
                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <h1>@yield('title', 'Page Title')</h1>
                                </div>
                            </div>
                        </div><!-- /.container-fluid -->
                    </section>
                    <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                           
                            <div class="col-12">
                                @yield('content')
                            </div>  <!--col -->
                        </div>  <!--row -->
                    </div>   <!--container-fluid-->   
                </section>
            </div>
        </div>
        <!-- Footer -->
        @include('includes.footer')

        <script src="{{asset('js/app.js')}}"></script>
        <script src="{{asset('js/functions.js')}}"></script>

        <script src="https://openlayers.org/en/v4.6.5/build/ol.js"></script>
        <script>
            $(document).ready(function() {
                $('[data-toggle="collapse"]').click(function() {
                    $(this).toggleClass( "active" );
                    if ($(this).hasClass("active")) {
                    $(this).text( __('Hide Filter') );
                    } else {
                    $(this).text( __('Show Filter') );
                    }
                });
            });
        </script> 
        @stack('scripts')
        @include('toast-message')
    </body>
</html>