<a class="navbar-brand" id="goHome" href="/">
    <span class="logo-contain">
    	<img src="{!! asset('vendor/hdmaster/core/img/logo.png') !!}" alt="Testmaster Universe">
    </span>

    @if($abbrev == 'ZZ')
		{{-- If it's our testing state, simply show a label --}}
		<span class="state-label-display label label-success">{{ $abbrev }}</span>
    @else
    	{{-- Show the state's shape --}}
	    <span class="state-display stateface stateface-{{{ strtolower($abbrev) }}}">
	    	<span>{{ $abbrev }}</span>
	    </span>
    @endif
</a>
