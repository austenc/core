@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-md-9">
			<p class="lead">Disciplines</p>
			<div class="form-group">
				{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
				<div class="input-group">
					{!! Form::text('search', Input::get('search'), 
					['placeholder' => 'Search for Disciplines', 'autofocus' => 'autofocus']) !!}
					<span class="input-group-btn">
						<button class="btn btn-info" type="submit">
							{!! Icon::search() !!} <span class="sr-only">Search</span>
						</button>
      				</span>
				</div>
			</div>
			 
			<div class="well table-responsive">
	      		<table class="table table-striped">
	      			<thead>
						<tr>
							<th>Name</th>
							<th>Content</th>
						</tr>
					</thead>
					@foreach ($disciplines as $discipline)
					<tr>
						<td>
							<a href="{{ route('discipline.edit', $discipline->id) }}">
								{{ $discipline->name }}
							</a><br>
							<small>{{ $discipline->abbrev }}</small>
						</td>

						<td>
							@foreach($discipline->exams as $exam)
								{{ $exam->name }}<br>
								<small>Knowledge</small><br>
							@endforeach

							@foreach($discipline->skills as $skill)
								{{ $skill->name }}<br>
								<small>Skill</small><br>
							@endforeach

							@foreach($discipline->training as $training)
								{{ $training->name }}<br>
								<small>Training</small><br>
							@endforeach
						</td>
					</tr>
					@endforeach
		      	</table>
	      	</div>
		</div>

		{{-- Sidebar --}}
		@include('core::discipline.sidebars.index')
	</div>
@stop