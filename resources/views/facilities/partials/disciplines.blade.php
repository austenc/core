<h3 id="discipline-info">Disciplines</h3>
<div class="well">
	<span class="text-danger">{{ $errors->first('discipline_id') }}</span>
	<table class="table table-striped" id="discipline-table">
		<thead>
			<tr>
				<th></th>
				<th>Name</th>
				<th>Parent</th>
			</tr>
		</thead>
		<tbody>
			@foreach($disciplines as $d)
				<tr>
					<td>{!! Form::checkbox('discipline_id[]', $d->id) !!}</td>
					
					{{-- Discipline Name --}}
					<td>
						{{ $d->name }}<br>
						<small>{{ $d->abbrev }}</small>
					</td>

					<td>
						{!! Form::select('discipline_parent['.$d->id.']', $avParents[$d->id]) !!}
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</div>