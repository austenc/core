<h3 id="testforms">Testforms</h3>
<div class="well">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Name</th>
				<th>Status</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			@foreach($item->testforms as $form)
			@if($form->status == 'active')
			<tr class="success">
			@elseif($form->status == 'archived')
			<tr class="danger">
			@else
			<tr class="warning">
			@endif
				<td>
					<a href="{{ route('testforms.edit', $form->id) }}">{{ $form->name }}</a>
				</td>

				<td>
					@if($form->status == 'active')
					<span class="label label-success">
					@elseif($form->status == 'archived')
					<span class="label label-danger">
					@else
					<span class="label label-warning">
					@endif
						{{ ucfirst($form->status) }}
					</span>
				</td>

				<td>
					<div class="btn-group pull-right">
						@if($form->oral == 1)
							<a class="btn btn-link" data-toggle="tooltip" title="Oral">{!! Icon::volume_up() !!}</a>
						@else
							<a class="btn btn-link" data-toggle="tooltip" title="No Oral">{!! Icon::volume_off() !!}</a>
						@endif

						@if($form->spanish == 1)
							<a class="btn btn-link" data-toggle="tooltip" title="Spanish">{!! Icon::globe() !!}</a>								
						@endif
					</div>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</div>