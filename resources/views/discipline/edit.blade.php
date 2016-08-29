@extends('core::layouts.default')

@section('content')
	<div class="row">
		{!! Form::model($discipline, ['route' => ['discipline.update', $discipline->id], 'method' => 'PUT']) !!}
		<div class="col-md-9">
			<div class="row">
                <div class="col-xs-8">
                    <h1>Edit Discipline</h1>
                </div>
                {!! HTML::backlink('discipline.index') !!}
            </div>

			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active">
					<a href="#discipline-info" aria-controls="discipline info" role="tab" data-toggle="tab">
						{!! Icon::info_sign() !!} Discipline Info
					</a>
				</li>
				<li role="presentation">
					<a href="#discipline-facilities" aria-controls="facilities" role="tab" data-toggle="tab">
						{!! Icon::home() !!} {{ Lang::choice('core::terms.facility', 2) }}
					</a>
				</li>
				<li role="presentation">
					<a href="#discipline-instructors" aria-controls="instructors" role="tab" data-toggle="tab">
						{!! Icon::education() !!} {{ Lang::choice('core::terms.instructor', 2) }}
					</a>
				</li>
			</ul>
			<div class="tab-content well">
			    <div role="tabpanel" class="tab-pane active" id="discipline-info">
					<h3 id="info">Information</h3>
					<div class="well">
						<div class="form-group row">
							<div class="col-md-12">
								{!! Form::label('name', 'Name') !!} @include('core::partials.required')
								{!! Form::text('name') !!}
								<span class="text-danger">{{ $errors->first('name') }}</span>
							</div>
						</div>

						<div class="form-group row">
		                    <div class="col-md-12">
						      	{!! Form::label('abbrev', 'Abbreviation') !!} @include('core::partials.required')
								{!! Form::text('abbrev') !!}
								<span class="text-danger">{{ $errors->first('abbrev') }}</span>
							</div>
						</div>
						
						<div class="form-group row">
							<div class="col-md-12">
								{!! Form::label('description', 'Description') !!}
								{!! Form::textarea('description') !!}
								<span class="text-danger">{{ $errors->first('description') }}</span>
							</div>
						</div>
			      	</div>

			      	<h3 id="content">Content</h3>
			      	<div class="well table-responsive">
			      		@if($discipline->training->isEmpty() && $discipline->skills->isEmpty() && $discipline->exams->isEmpty())
						No Content
			      		@else
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Name</th>
									<th>Type</th>
								</tr>
							</thead>
							<tbody>
								@foreach($discipline->training as $training)
									<tr>
										<td><a href="{{ route('trainings.edit', $training->id) }}">{{ $training->name }}</a></td>
										<td>Training</td>
									</tr>
								@endforeach

								@foreach($discipline->skills as $skill)
								<tr>
									<td><a href="{{ route('skillexams.edit', $skill->id) }}">{{ $skill->name }}</a></td>
									<td>Skill</td>
								</tr>
								@endforeach

								@foreach($discipline->exams as $exam)
								<tr>
									<td><a href="{{ route('exams.edit', $exam->id) }}">{{ $exam->name }}</a></td>
									<td>Knowledge</td>
								</tr>
								@endforeach
							</tbody>
						</table>
						@endif
					</div>
				</div>

				<div role="tabpanel" class="tab-pane" id="discipline-facilities">
					<h3 id="facilities">{{ Lang::choice('core::terms.facility', 2) }}</h3>
					<div class="well table-responsive">
						@if($discipline->facilities->isEmpty())
							No {{ Lang::choice('core::terms.facility', 2) }}
						@else
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Name</th>
									<th>TM License</th>
									<th>Old License</th>
									<th>Training</th>
									<th>Testing</th>
								</tr>
							</thead>
							<tbody>
								@foreach($discipline->facilities as $facility)
								<tr>
									<td>
										<a href="{{ route('facilities.edit', $facility->id) }}">
											{{ $facility->name }}
										</a>
									</td>
									<td class="monospace">{{ $facility->pivot->tm_license }}</td>
									<td class="monospace">{{ $facility->pivot->old_license }}</td>
									<td>
										@if(in_array('Training', $facility->actions))
											{!! Icon::ok() !!}
										@endif
									</td>
									<td>
										@if(in_array('Testing', $facility->actions))
											{!! Icon::ok() !!}
										@endif
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
						@endif
					</div>
				</div>

				<div role="tabpanel" class="tab-pane" id="discipline-instructors">
					<h3 id="instructors">{{ Lang::choice('core::terms.instructor', 2) }}</h3>
					<div class="well table-responsive">
						@if($discipline->instructors->isEmpty())
							No {{ Lang::choice('core::terms.instructor', 2) }}
						@else
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Name</th>
									<th>RN License</th>
								</tr>
							</thead>
							<tbody>
								@foreach($discipline->instructors as $instructor)
								<tr>
									<td><a href="{{ route('instructors.edit', $instructor->id) }}">{{ $instructor->full_name }}</a></td>
									<td class="monospace">{{ $instructor->license }}</td>
								</tr>
								@endforeach
							</tbody>
						</table>
						@endif
					</div>
				</div>
			</div>
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			@include('core::discipline.sidebars.edit')
		</div>
		{!! Form::close() !!}
	</div>
@stop