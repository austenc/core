@extends('core::layouts.default')

@section('content')
	{!! Form::model($exam, ['route' => ['exams.update', $exam->id], 'method' => 'PUT']) !!}

		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Edit Exam</h1>
				</div>
				{!! HTML::backlink('exams.index') !!}
			</div>

			{{-- Warnings --}}
			@if($exam->subjects->isEmpty())
			<div class="alert alert-warning">
				{!! Icon::flag() !!} <strong>No Subjects</strong> Unable to generate Testforms without defined Subjects
			</div>
			@endif

			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active">
					<a href="#exam-info" aria-controls="exam info" role="tab" data-toggle="tab">
						{!! Icon::info_sign() !!} Exam Info
					</a>
				</li>
				<li role="presentation">
					<a href="#exam-reqs" aria-controls="test forms" role="tab" data-toggle="tab">
						{!! Icon::flag() !!} Requirements
					</a>
				</li>
				<li role="presentation">
					<a href="#exam-testforms" aria-controls="test forms" role="tab" data-toggle="tab">
						{!! Icon::list_alt() !!} Test Forms
					</a>
				</li>
				<li role="presentation">
					<a href="#exam-subjects" aria-controls="test forms" role="tab" data-toggle="tab">
						{!! Icon::th_large() !!} Subjects
					</a>
				</li>
			</ul>
			<div class="tab-content well">
			    
				{{-- Basic Information --}}
			    <div role="tabpanel" class="tab-pane active" id="exam-info">
					<h3>Basic Info</h3>
					<div class="well">
						<div class="form-group row">
							<div class="col-md-8">
								{!! Form::label('name', 'Name') !!} @include('core::partials.required')
								{!! Form::text('name') !!}
								<span class="text-danger">{{ $errors->first('name') }}</span>
							</div>

							<div class="col-md-4">
								{!! Form::label('abbrev', 'Abbrev') !!} @include('core::partials.required')
								{!! Form::text('abbrev') !!}
							</div>
						</div>

						<div class="form-group row">
							<div class="col-md-12">
								{!! Form::label('discipline_id', 'Discipline') !!}
								{!! Form::text('discipline', $exam->discipline->name, ['disabled']) !!}
								{!! Form::hidden('discipline_id', $exam->discipline->id) !!}
							</div>
						</div>

						<hr>
						
						<div class="form-group row">
							<div class="col-md-12">
								{!! Form::label('max_attempts', 'Max Attempts') !!}
								{!! Form::text('max_attempts') !!}
								<span class="text-danger">{{ $errors->first('max_attempts') }}</span>
							</div>
						</div>

						<div class="form-group row">
							<div class="col-md-12">
								{!! Form::label('price', 'Price') !!}
							    <div class="input-group">
							        <div class="input-group-addon">$</div>
							        {!! Form::text('price', null, ['class' => 'form-control']) !!}
							    </div>
								<span class="text-danger">{{ $errors->first('price') }}</span>
							</div>
						</div>

						<div class="form-group row">
							<div class="col-md-12">
								{!! Form::label('has_paper', 'Has Paper?') !!}
								{!! Form::select('has_paper', [0 => 'No', 1 => 'Yes']) !!}
								<span class="text-danger">{{ $errors->first('has_paper') }}</span>
							</div>
						</div>
					</div>
	

					<h3>Notes</h3>
					<div class="well">
						<div class="form-group">
							<textarea name="comments" id="comments" class="form-control">{{ $exam->comments }}</textarea>
						</div>
					</div>
				</div>

				{{-- Requirements --}}			
				<div role="tabpanel" class="tab-pane" id="exam-reqs">
					<h3>Requirements</h3>
					<div class="well">
						<h4>Knowledge Exams</h4>
						<table class="table table-striped" id="req-exam-table">
							<tbody>
								@foreach ($discipline->exams as $ex)
									@if(in_array($ex->id, $exam->required_exams->lists('id')->all()))
									<tr class="warning">
									@else
									<tr>
									@endif
										<td>
											@if(in_array($ex->id, $exam->required_exams->lists('id')->all()))
											<a title="Required" data-toggle="tooltip">{!! Icon::star() !!}</a>
											@endif
											{{ $ex->name }}
										</td>
										<td>
											{!! Form::select('req_exam_id['.$ex->id.']', [0 => 'Not Required', 1 => 'Required'], in_array($ex->id, $exam->required_exams->lists('id')->all())) !!}
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>

						<hr>

						<h4>Skill Exams</h4>
						<table class="table table-striped" id="req-skill-table">
							<tbody>
								@foreach ($discipline->skills as $sk)
									@if(in_array($sk->id, $exam->required_skills->lists('id')->all()) || in_array($sk->id, $exam->corequired_skills->lists('id')->all()))
									<tr class="warning">
									@else
									<tr>
									@endif
										<td>
											@if(in_array($sk->id, $exam->required_skills->lists('id')->all()))
											<a title="Required" data-toggle="tooltip">{!! Icon::star() !!}</a>
											@elseif(in_array($sk->id, $exam->corequired_skills->lists('id')->all()))
											<a title="Corequired" data-toggle="tooltip">{!! Icon::star() !!}</a>
											@endif
											{{ $sk->name }}
										</td>
										<td>
											@if(in_array($sk->id, $exam->required_skills->lists('id')->all()))
												{!! Form::select('req_skill_id['.$sk->id.']', [0 => 'Not Required', 1 => 'Required', 2 => 'Corequired'], 1) !!}
											@elseif(in_array($sk->id, $exam->corequired_skills->lists('id')->all()))
												{!! Form::select('req_skill_id['.$sk->id.']', [0 => 'Not Required', 1 => 'Required', 2 => 'Corequired'], 2) !!}
											@else
												{!! Form::select('req_skill_id['.$sk->id.']', [0 => 'Not Required', 1 => 'Required', 2 => 'Corequired'], 0) !!}
											@endif
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>

						<hr>

						<h4>Trainings</h4>
						<table class="table table-striped" id="req-training-table">
							<tbody>
								@foreach ($discipline->training as $tr)
									@if(in_array($tr->id, $exam->required_trainings->lists('id')->all()))
									<tr class="warning">
									@else
									<tr>
									@endif
										<td>
											@if(in_array($tr->id, $exam->required_trainings->lists('id')->all()))
											<a title="Required" data-toggle="tooltip">{!! Icon::star() !!}</a>
											@endif
											{{ $tr->name }}
										</td>
										<td>{!! Form::select('req_training_id['.$tr->id.']', [0 => 'Not Required', 1 => 'Required'], in_array($tr->id, $exam->required_trainings->lists('id')->all())) !!}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>

				{{-- Testforms --}}			
				<div role="tabpanel" class="tab-pane" id="exam-testforms">
					<h3>Testforms</h3>
					<div class="well">
						<table class="table table-striped row-sel" id="skilltests-table">
							<thead>
								<tr>
									<th>#</th>
									<th>Name</th>
									<th>Minimum</th>
									<th>Testitems</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
							@foreach($exam->testforms as $testform)
								@if($testform->status == 'draft')
								<tr class="warning">
								@elseif($testform->status == 'active')
								<tr class="success">
								@else
								<tr class="danger">
								@endif
									<td>
										<span class="lead text-muted">{{ $testform->id }}</span>
									</td>

									<td>
										<a href="{{ route('testforms.edit', $testform->id) }}">
											{{ $testform->name }}
										</a><br>

										@if($testform->status == 'active')
										<span class="label label-success">
										@elseif($testform->status == 'archived')
										<span class="label label-danger">
										@else
										<span class="label label-warning">
										@endif
											{{ ucfirst($testform->status) }}
										</span>
									</td>

									<td class="monospace">{{ $testform->minimum }}</td>
									<td class="monospace">{{ $testform->testitems->count() }}</td>

									<td>
										<div class="btn-group pull-right">
											@if($testform->getOriginal('oral'))
												<a title="Oral Testform" data-toggle="tooltip" class="btn btn-link">
													{!! Icon::volume_up() !!}
												</a>
											@endif

											@if($testform->getOriginal('spanish'))
												<a title="Spanish Testform" data-toggle="tooltip" class="btn btn-link">
													{!! Icon::globe() !!}
												</a>
											@endif
										</div>
									</td>
								</tr>
							@endforeach
							</tbody>
						</table>
					</div>
				</div>

				{{-- Subjects --}}			
				<div role="tabpanel" class="tab-pane" id="exam-subjects">
					<h3>Subjects</h3>
					<div class="well">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>#</th>
									<th>Name</th>
									<th>Report As</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								@foreach($exam->subjects as $subject)
									<tr>
										<td><span class="lead text-muted">{{ $subject->id }}</span></td>

										<td>
											<a href="{{ route('subjects.edit', $subject->id) }}">
												{{ $subject->name }}
											</a>
										</td>

										<td>
											@if($subject->report_as)
												<a href="{{ route('subjects.edit', $subject->report_as) }}">
													{{ $subject->reportAs->name }}
												</a>
											@else
												<p class="text-muted">Self</p>
											@endif
										</td>

										<td>
											<div class="btn-group pull-right">
												<a data-confirm="Delete subject {{{ $subject->name }}}?<br><br>Are you sure?" href="{{ route('exams.subject.remove', [$exam->id, $subject->id]) }}" class="btn btn-danger btn-sm remove-subject">Remove</a>
											</div>
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
						{{-- core::exams.partials.subjects') --}}
					</div>
				</div>

			</div>			
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
				{!! Button::success(Icon::refresh().' Update')->submit() !!}
	
				<a href="{{ route('subjects.create', ['exam' => $exam->id]) }}" class="btn btn-default">{!! Icon::plus_sign() !!} Add Subject</a>
			</div>
		</div>
	{!! Form::close() !!}
@stop