@if(Auth::user()->ability(['Admin', 'Staff'], []) && $facility->agency_only)
	<div class="alert alert-warning">
		{!! Icon::flag()  !!}<strong>Agency Only</strong> {{ Lang::choice('core::terms.facility', 1) }} is available for Agency use only.
	</div>
@endif