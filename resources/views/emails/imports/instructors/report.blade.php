<h2>Instructor Import Report</h2>
<p>The following Instructor records were processed at {{ date('m/d/Y H:i:s') }}.</p>

@if(isset($records['new']))
	<strong>New</strong>
	@foreach($records as $ins)
		{{ var_dump($ins) }}
	@endforeach
	<br><br>
@endif

@if(isset($records['duplicate']))
	<strong>Updated</strong>
	@foreach($records as $ins)
		{{ var_dump($ins) }}
	@endforeach
	<br><br>
@endif

@if(isset($records['invalid']['missingEmail']))
	<strong>Missing Email</strong>
	@foreach($records as $ins)
		{{ var_dump($ins) }}
	@endforeach
	<br><br>
@endif

@if(isset($records['invalid']['error']))
	<strong>Error</strong>
	@foreach($records as $ins)
		{{ var_dump($ins) }}
	@endforeach
	<br><br>
@endif

@if(isset($records['invalid']['exists']))
	<strong>Existing User Email</strong>
	@foreach($records as $ins)
		{{ var_dump($ins) }}
	@endforeach
	<br><br>
@endif


<strong>Login at {!! link_to('/') !!} to gain access to your account.</strong>