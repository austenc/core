@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'testitems.store']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Create New Testitem</h1>
			</div>
			{!! HTML::backlink('testitems.index') !!}
		</div>

		<h3 id="info">Information</h3>
		<div class="well">
			<div class="form-group">
				{!! Form::label('stem', 'Stem') !!} @include('core::partials.required') 
				<span class="text-danger">{{ $errors->first('stem') }}</span>
				{!! Form::textarea('stem') !!}
			</div>
		
			{{-- Distractors --}}
			{!! Form::label('distractors', 'Distractors') !!} @include('core::partials.required')
			<p class="text-danger">
				{{ $errors->first('distractors') }}
				{{ $errors->first('answer') }}
			</p>		
			@for($i=0; $i < strlen($options); $i++)
				<div class="form-group row">
					<div class="col-md-10 col-sm-10 col-xs-10">
						<div class="input-group">
							<div class="input-group-addon">{{ strtoupper($options[$i]) }}.</div>
							{!! Form::text('distractors['.$i.']') !!}
						</div>
					</div>								
					<div class="col-md-2 col-sm-2 col-xs-2 radio">
						{!! Form::radio('answer', $i) !!}
						Answer
					</div>
				</div>
			@endfor
		</div>

		{{-- Exams/Subjects --}}
		<h3 id="subjects">Exam Subjects</h3>
		<div class="well">
			@foreach($exams as $exam)
				<div class="form-group">
					{!! Form::label('subjects['.$exam->id.']', ucwords($exam->name)) !!}
					{!! Form::select(
						'subjects['.$exam->id.']', 
						array_map('ucwords', [0 => 'DISABLED'] + $exam->subjects->lists('name', 'id')->all())
					) !!}
				</div>
			@endforeach
		</div>

		<h3 id="other">Other</h3>
		<div class="well">
			{{-- Auth Source --}}
			<div class="form-group">
				{!! Form::label('auth_source', 'Authoritative Source') !!}
				{!! Form::select('auth_source', Config::get('core.knowledge.sources')) !!}
			</div>

			{{-- Enemies --}}
			<div class="form-group">
				<label for="enemies">Enemies <small>(Not With)</small></label> 
				<div class="input-group">
					{!! Form::text('enemies', null) !!}
					<span class="input-group-btn">
						<a href="{{ route('testitems.enemies') }}" class="btn btn-success" data-toggle="modal" data-target="#select-enemies">
							{!! Icon::plus() !!}
						</a>
					</span>
				</div>
			</div>		

			{{-- Vocab --}}
			<div class="form-group">
				{!! Form::label('vocab', 'Vocabulary') !!}
				<div class="input-group">
					{!! Form::text('vocab', null, ['data-provide' => 'typeahead', 'data-source' => $vocab, 'placeholder' => 'Type Word, Press Enter', 'autocomplete' => 'off']) !!}
					<span class="input-group-btn">
						<button class="btn btn-success add-vocab">
							{!! Icon::plus() !!}
						</button>
					</span>
				</div>
			</div>

			{{-- Comments --}}
			<div class="form-group">
				{!! Form::label('comments', 'Comments') !!}
				{!! Form::textarea('comments') !!}
			</div>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::testitems.sidebars.create')
	</div>
	{!! Form::close() !!}

	{!! HTML::modal('select-enemies') !!}

	@include('core::testitems.partials.vocab')

@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/testitems/selectEnemies.js') !!}
@stop