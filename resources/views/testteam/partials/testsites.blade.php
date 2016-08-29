@foreach($disciplines as $discipline)
	@if(Input::old('discipline_id') && in_array($discipline->id, Input::old('discipline_id')))
	<h3 class="test-site-header" id="discipline-{{{ $discipline->id }}}-test-site-title">
		{{ $discipline->abbrev }} {{ Lang::choice('core::terms.facility_testing', 2) }}
	</h3>
	<div class="well test-site-div" id="discipline-{{{ $discipline->id }}}-test-site">
	@else
	<h3 style="display:none;" class="test-site-header" id="discipline-{{{ $discipline->id }}}-test-site-title">
		{{ $discipline->abbrev }} {{ Lang::choice('core::terms.facility_testing', 2) }}
	</h3>
	<div style="display:none;" class="well test-site-div" id="discipline-{{{ $discipline->id }}}-test-site">
	@endif
		<span class="text-danger">{{ $errors->first('testsite_id') }}</span>
		<table class="table table-striped">
			<thead>
				<tr>
					<th></th>
					<th>Name</th>
				</tr>
			</thead>
			<tbody>
				@foreach($discipline->testSites as $i => $site)
					@if(Input::old('testsite_id') && in_array($discipline->id.'|'.$site->id, Input::old('testsite_id')))
					<tr class="success" data-clickable-row>
					@else
					<tr data-clickable-row>
					@endif
						<td>{!! Form::checkbox('testsite_id[]', $discipline->id.'|'.$site->id) !!}</td>
						<td>
							{{ $site->name }}<br>
							<small>#{{ $site->pivot->tm_license }}</small>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endforeach