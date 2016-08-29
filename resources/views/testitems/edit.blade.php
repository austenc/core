@extends('core::layouts.default')

@section('content')
	<div class="col-md-9">
		@include('core::testitems.partials.status')

	{!! Form::model($item, ['route' => ['testitems.update', $item->id], 'method' => 'PUT']) !!}

		<div class="row">
			<div class="col-xs-8">
				<h1>Edit Testitem</h1>
			</div>
			{!! HTML::backlink('testitems.index') !!}
		</div>

		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active">
				<a href="#item-info" aria-controls="item info" role="tab" data-toggle="tab">
					{!! Icon::info_sign() !!} Item Info
				</a>
			</li>
			<li role="presentation">
				<a href="#item-testforms" aria-controls="test forms" role="tab" data-toggle="tab">
					{!! Icon::list_alt() !!} Test Forms
				</a>
			</li>
			<li role="presentation">
				<a href="#item-statistics" aria-controls="statistics" role="tab" data-toggle="tab">
					{!! Icon::stats() !!} Statistics
				</a>
			</li>
		</ul>
		<div class="tab-content well">
		    <div role="tabpanel" class="tab-pane active" id="item-info">
		    	<h3 id="info">Information</h3>
		    	<div class="well">
		    		{{-- Stem --}}
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
		    		@for($i=0; $i < count($item->distractors); $i++)
		    			<div class="form-group row {{ $item->answer == $item->distractors->get($i)->id ? 'has-success has-feedback' : '' }}">
		    				<div class="col-md-10 col-sm-10 col-xs-10">
		    					<div class="input-group">
		    						<div class="input-group-addon">{{ strtoupper($options[$i]) }}.</div>
		    						@if($item->distractors->has($i))
		    							{!! Form::text('distractors['.$i.']', $item->distractors->get($i)->content) !!}
		    						@endif
		    					</div>
		    				</div>								
		    				<div class="col-md-2 col-sm-2 col-xs-2 radio">
		    					@if($item->distractors->has($i))
		    						{!! Form::radio('answer', $item->distractors->get($i)->id, $item->answer == $item->distractors->get($i)->id) !!}
		    					@endif
		    					Answer
		    				</div>
		    			</div>
		    		@endfor
		    	</div>

		    	{{-- Exams / Subjects --}}
		    	<h3 id="subjects">Exams / Subjects</h3>
		    	<div class="well">
		    		@foreach($exams as $exam)
		    			<div class="form-group">
		    				{!! Form::label('subjects['.$exam->id.']', ucwords($exam->name)) !!}
		    				{!! Form::select(
		    					'subjects['.$exam->id.']', 
		    					array_map('ucwords', [0 => 'DISABLED'] + $exam->subjects->lists('name', 'id')->all()), 
		    					array_key_exists($exam->id, $item_subjects) ? $item_subjects[$exam->id] : null 
		    				) !!}
		    			</div>
		    		@endforeach
		    	</div>

		    	{{-- Other --}}
		    	<h3 id="other">Other</h3>
		    	<div class="well">
		    		<div class="form-group">
		    			{!! Form::label('auth_source', 'Authoritative Source') !!}
		    			{!! Form::select('auth_source', Config::get('core.knowledge.sources'), $item->auth_source) !!}
		    		</div>
		    		
		    		{{-- Enemies --}}
		    		<div class="form-group">
		    			<label for="enemies">Enemies <small>(Not With)</small></label> 
		    			<div class="input-group">
		    				{!! Form::text('enemies', $enemies) !!}
		    				<span class="input-group-btn">
		    					<a href="{{ route('testitems.enemies', $item->id) }}" class="btn btn-success" data-toggle="modal" data-target="#select-enemies">
		    						{!! Icon::plus() !!}
		    					</a>
		    				</span>
		    			</div>
		    		</div>		
		    	
		    		{{-- Vocab --}}
		    		<div class="form-group">
		    			{!! Form::label('vocab', 'Vocabulary') !!}
		    			<div class="input-group">
		    				{!! Form::text('vocab', implode(',', $item->vocab()->lists('word')->all()), ['data-provide' => 'typeahead', 'data-source' => $vocab, 'placeholder' => 'Type Word, Press Enter', 'autocomplete' => 'off']) !!}
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

			<div role="tabpanel" class="tab-pane" id="item-testforms">			
				{{-- Testforms --}}
				@include('core::testitems.partials.testforms')
			</div>

			<div role="tabpanel" class="tab-pane" id="item-statistics">			
				{{-- Statistics --}}
				@include('core::testitems.partials.statistics')
			</div>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::testitems.sidebars.edit')
	</div>

	{!! Form::close() !!}
	{!! HTML::modal('select-enemies') !!}
@stop

@section('scripts')
	@include('core::testitems.partials.vocab')
	{!! HTML::script('vendor/hdmaster/core/js/testitems/selectEnemies.js') !!}
@stop