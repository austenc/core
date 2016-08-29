<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>
		Maintenance | Testmaster
	</title>

	{{-- Outdated browser notice --}}
	{!! HTML::style('vendor/outdated-browser/outdatedbrowser/outdatedbrowser.min.css') !!}

	{!! HTML::style('css/style.min.css') !!}

	<!-- Favicon / Icons -->
	<link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
	<link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="/favicon-194x194.png" sizes="194x194">
	<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
	<link rel="icon" type="image/png" href="/android-chrome-192x192.png" sizes="192x192">
	<link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
	<link rel="manifest" href="/manifest.json">
	<meta name="msapplication-TileColor" content="#00a300">
	<meta name="msapplication-TileImage" content="/mstile-144x144.png">
	<meta name="theme-color" content="#ffffff">
</head>

<body id="index" class="maintenance-mode">

	<div class="main">
		<div class="container">
			<!-- Main Content -->
			@yield('content')
		</div>
	</div>
	
	<!-- Footer -->
	@include('core::layouts.footer')

	{{-- Outdated browser --}}
	<div id="outdated"></div>

	{!! HTML::script('vendor/outdated-browser/outdatedbrowser/outdatedbrowser.min.js') !!}

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