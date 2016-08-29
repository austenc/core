<h2>Facility Import Report</h2>
<p>The following Facility records were processed at {{ date('m/d/Y H:i:s') }}.</p>

@if(isset($records['new']))
	<strong>New</strong>
	@foreach($records as $fac)
		{{ var_dump($fac) }}
	@endforeach
	<br><br>
@endif