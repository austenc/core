@if(Auth::user()->ability(['Admin', 'Staff'], []))
	{{-- Show warning if instructor is set to expire in the next week --}}
	@if($instructor->expires && ($instructor->expires_in_days < 7))
		<div class="alert alert-warning">
			<strong>Warning! </strong>This record expires in {{ $instructor->expires_in_days }} day{{ $instructor->expires_in_days > 1 ? 's' : '' }}. All login capabilities will be removed.
		</div>
	@endif
@endif