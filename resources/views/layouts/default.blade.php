<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <title>
        @yield('title', $title) @if($title) | @endif Testmaster
    </title>

    {{-- Outdated browser notice --}}
    {!! HTML::style('vendor/outdated-browser/outdatedbrowser/outdatedbrowser.min.css') !!}


    {{-- Main styles and Print styles --}}
    {!! HTML::style('css/style.min.css') !!}
    {!! HTML::style('css/print.min.css') !!}

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">

    {{-- Calendar --}}
    @if(isset($includeCalendar))
        {!! HTML::style('vendor/fullcalendar/dist/fullcalendar.css') !!}
    @endif

    <!-- Theme style -->
    {{-- Now included in main style.less --}}
    {{-- HTML::style('vendor/AdminLTE/dist/css/AdminLTE.min.css') --}}
    {!! HTML::style('vendor/AdminLTE/dist/css/skins/skin-blue.min.css') !!}

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    @include('core::partials.favicons')
</head>
<?php /**
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
*/
?>
<body class="hold-transition skin-blue sidebar-mini {{{ $bodyClass }}}" id="index">
<div class="wrapper">

    <!-- Main Header -->
    <header class="main-header">

        <!-- Logo -->
        <a href="/" class="logo" id="goHome">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>TMU</b></span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg">
                <img src="{!! asset('vendor/hdmaster/core/img/logo-white.png') !!}" alt="Testmaster Universe" class="img-responsive">
            </span>
        </a>

        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle no-load" data-toggle="offcanvas" role="button">
                <i class="glyphicon glyphicon-menu-hamburger"></i>
                <span class="sr-only">Toggle navigation</span>
            </a>
            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    @include('core::navigation.partials.top')

                    <?php /**
                    <!-- Control Sidebar Toggle Button -->
                    <li>
                        <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                    </li>
        */
    ?>
                </ul>
            </div>
        </nav>
    </header>
    
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">

        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">

            @include('core::navigation.menu')

        </section>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        <!-- Flash Messages -->
        @include('core::partials.flash_messages')
        
        {{-- Show Pending/Started tests if applicable --}}
        @include('core::partials.actionable_tests')

        <!-- Content Header (Page header) -->
        {{-- 
        <section class="content-header">
            <h1>
                Page Header
                <small>Optional description</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol>
        </section>
         --}}

        <!-- Main content -->
        <section class="content">

            <div class="main">
                <div class="container-fluid">
                    <!-- Main Content -->
                    @yield('content')
                    @yield('sidebar')
                </div>
            </div>

        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer">
        @include('core::layouts.footer')
    </footer>

<?php 
/**
    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Create the tabs -->
        <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
            <li class="active"><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
            <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
        </ul>
        <!-- Tab panes -->
        <div class="tab-content">
            <!-- Home tab content -->
            <div class="tab-pane active" id="control-sidebar-home-tab">
                <h3 class="control-sidebar-heading">Recent Activity</h3>
                <ul class="control-sidebar-menu">
                    <li>
                        <a href="javascript::;">
                            <i class="menu-icon fa fa-birthday-cake bg-red"></i>

                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>

                                <p>Will be 23 on April 24th</p>
                            </div>
                        </a>
                    </li>
                </ul>
                <!-- /.control-sidebar-menu -->

                <h3 class="control-sidebar-heading">Tasks Progress</h3>
                <ul class="control-sidebar-menu">
                    <li>
                        <a href="javascript::;">
                            <h4 class="control-sidebar-subheading">
                                Custom Template Design
                                <span class="label label-danger pull-right">70%</span>
                            </h4>

                            <div class="progress progress-xxs">
                                <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
                            </div>
                        </a>
                    </li>
                </ul>
                <!-- /.control-sidebar-menu -->

            </div>
            <!-- /.tab-pane -->
            <!-- Stats tab content -->
            <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
            <!-- /.tab-pane -->
            <!-- Settings tab content -->
            <div class="tab-pane" id="control-sidebar-settings-tab">
                <form method="post">
                    <h3 class="control-sidebar-heading">General Settings</h3>

                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Report panel usage
                            <input type="checkbox" class="pull-right" checked>
                        </label>

                        <p>
                            Some information about this general settings option
                        </p>
                    </div>
                    <!-- /.form-group -->
                </form>
            </div>
            <!-- /.tab-pane -->
        </div>
    </aside>
    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
             immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
*/ ?>

</div>
<!-- ./wrapper -->

{{-- Outdated browser --}}
<div id="outdated"></div>

{{-- AJAX loading indicator --}}
{{-- Flash Messages (overlay style, JS) --}}
@include('core::partials.ajax_loading')
@include('core::partials.flash_overlay')

{{-- Modal for session timeout --}}
@if(Auth::check())
    @include('core::partials.session_timeout', ['id' => 'session-timeout-modal'])
@endif

{{-- Show a warning if they're on the staging server --}}
@if(App::environment('staging'))
    @include('core::partials.staging_warning')
@endif

<!-- REQUIRED JS SCRIPTS -->
{{-- Outdated browser script --}}
{!! HTML::script('vendor/outdated-browser/outdatedbrowser/outdatedbrowser.min.js') !!}

{{-- TMU Scripts --}}
{!! HTML::script('js/scripts.min.js') !!}

{{-- Calendar plugin --}}
@if(isset($includeCalendar))
    {!! HTML::script('vendor/moment/min/moment.min.js') !!}
    {!! HTML::script('vendor/fullcalendar/dist/fullcalendar.min.js') !!}
@endif

{{-- Page-specific scripts --}}
@yield('scripts')

{{-- Outdated browser warning --}}
<script type="text/javascript">
    //event listener: DOM ready
    function addLoadEvent(func) {
        var oldonload = window.onload;
        if (typeof window.onload != 'function') {
            window.onload = func;
        } else {
            window.onload = function() {
                oldonload();
                func();
            }
        }
    }

    //call plugin function after DOM ready
    addLoadEvent(
        outdatedBrowser({
            bgColor: '#f25648',
            color: '#ffffff',
            lowerThan: 'transform',
            languagePath: '/vendor/outdated-browser/outdatedbrowser/lang/en.html'
        })
    );
</script>

</body>
</html>
