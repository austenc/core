@if(Auth::user()->ability(['Admin', 'Staff'], []))
<h4>Test Team</h4>
<table class="table table-striped" id="disc-{{ strtolower($discipline->abbrev) }}-test-team-table">
	<thead>
		<tr>
			<th>Name</th>
			<th>TM License</th>
			<th>Type</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		@include('core::facilities.partials.people_row', ['items' => $facility->actors->sortBy('last'), 'type' => 'actor'])

		@include('core::facilities.partials.people_row', ['items' => $facility->observers->sortBy('last'), 'type' => 'observer'])

		@include('core::facilities.partials.people_row', ['items' => $facility->proctors->sortBy('last'), 'type' => 'proctor'])
	</tbody>
</table>
@endif