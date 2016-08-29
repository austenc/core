@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => 'testing.save', 'class' => 'form-horizontal', 'id' => 'testing_form']) !!}	
	<h2>
		{{ $exam->name }} <small class="pull-right time-remaining">Remaining: <span id="time-remaining">{{ gmdate('H:i:s', $time_remaining) }}</span>{!! Form::hidden('remaining', $time_remaining, ['id' => 'remaining']) !!}</small> 
	</h2>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<div class="row">
				<div class="col-xs-5">
					<span class="lead">{{ $student->fullname }}</span>
				</div>
				<div class="col-xs-2">
					<div class="input-group">
						{!! Form::text('jump_to', null, ['class' => 'input-sm jump-to-text', 'placeholder' => 'Jump to Question']) !!}
						<div class="input-group-btn">
							<button type="submit" id="jump-to-btn" class="btn btn-sm btn-warning btn-ajax ladda-button" data-style="zoom-in" data-size="s">Go</button>
						</div>
					</div>
				</div>
				<div class="col-xs-5 text-right">
					<a class="btn btn-sm btn-success" data-toggle="modal" data-target="#help-modal">Get Help</a>				
					<button type="submit" id="end-test" name="end-test" value="End Test" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#stop-modal">{!! Icon::floppy_save() !!} End Test</button>	
				</div>
			</div>
		</div>
		<div class="panel-body">
			{{-- Stem --}}
			<div class="h3 well testing-stem">#{{ $current }}. {{ $question->stem }}</div>

			{{-- Distractors --}}
			<div class="distractors-list">
				@foreach($question->distractors as $k => $d)
					<div class="form-group">
						<div class="col-xs-1 text-right distractor-label">{{ strtoupper(Config::get('core.knowledge.options')[$k]) }}.</div> 
						<div class="col-xs-11">
							<label class="radio bg-info distractor">
								{!! Form::radio('answer', $d->id, Session::get('testing.answers.'.$question->id) == $d->id) !!} <span class="distractor-content">{{ $d->content }}</span>
							</label>
						</div>
					</div>
				@endforeach
			</div>
		</div>
		<div class="panel-footer">
			<div class="row">
				<div class="col-xs-5">
					<button name="prev" id="prev" value="prev" data-style="expand-right" data-size="s"
					class="btn btn-sm btn-ajax ladda-button {{ $current > 1 ? 'btn-primary' : 'btn-disabled' }}" 
					{{ $current == 1 ? ' disabled="disabled"' : '' }} >{!! Icon::arrow_left() !!} Prev</button>
				</div>
				<div class="col-xs-2 text-center">
					<div class="form-group">
						<div class="checkbox text-left">
							<label for="bookmark"> 
								{!! Form::checkbox('bookmark', $current, in_array($current, $bookmarks), ['id' => 'bookmark']) !!} Bookmark This Question
							</label>
						</div>
					</div>
				</div>
				<div class="col-xs-5">
					<button name="next" id="next" value="next"  data-style="expand-left" data-size="s"
						class="btn btn-sm btn-ajax ladda-button pull-right {{ $current == $total ? 'btn-disabled' : 'btn-primary' }}" 
						 {{ $current == $total ? ' disabled="disabled"' : '' }}>
							<span class="ladda-label">Next {!! Icon::arrow_right() !!}</span>
						</button>
				</div>
			</div>
		</div>
	</div>

	{{-- Bookmarks  / Questions Remaining--}}
	<div class="row">
		<div class="col-xs-6">
			<span class="lead">Bookmarks</span>
			<div class="well well-sm bookmarks">
				@if($bookmarks)
					@foreach($bookmarks as $b)
						<a href="{{ route('testing.index', $b) }}" class="btn btn-sm {{ $b == $current ? 'btn-info' : 'btn-primary' }}">{{ $b }}</a>
					@endforeach
				@else
					<small class="text-muted">None.</small>
				@endif
			</div>
		</div>
		<div class="col-xs-6">
			<span class="lead">Questions Remaining</span>
			<div class="well well-sm text-center">
				@if($remaining)
					<small class="questions-remaining"> {!! implode(', ', $remaining) !!}</small>
				@else
					<small class="text-muted">None. Please Review your answers and click 'End Test' when finished.</small>
				@endif
			</div>
		</div>
	</div>
	<hr>
	<p class="lead text-center">There are keyboard shortcuts for test navigation. <a data-toggle="modal" class="btn-link" data-target="#help-modal">Learn More</a></p>

{!! Form::hidden('exam_id', $exam->id) !!}
{!! Form::hidden('current', $current, ['id' => 'current-question']) !!}
{!! Form::hidden('reloadValue', "", ['id' => 'reloadValue']) !!}

{!! Form::close() !!}


{{-- Help Modal --}}
<div class="modal fade modal-preserve" id="help-modal">
  	<div class="modal-dialog">
    	<div class="modal-content">
      		<div class="modal-header">
        		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        		<h4 class="modal-title">Testing Help</h4>
     		</div>

	      	<div class="modal-body">
	      		<span class="lead">Keyboard Shortcuts</span>
	        	<table class="table table-hover table-striped">
	        		<thead>
	        			<tr>
	        				<th>Action</th>
	        				<th>Key(s)</th>
	        			</tr>
	        		</thead>
	        		<tbody>
		        		<tr>
		        			<td>Answers</td>
		        			<td class="monospace">1,2,3,4 <em>or</em> A,B,C,D</td>
		        		</tr>
		        		<tr>
		        			<td>Next Question</td>
		        			<td class="monospace">Right Arrow / PageUp</td>
		        		</tr>
		        		<tr>
		        			<td>Previous Question</td>
		        			<td class="monospace">Left Arrow / PageDown</td>
		        		</tr>
		        		<tr>
		        			<td>Next Unanswered / Jump To</td>
		        			<td class="monospace">J</td>
		        		</tr>
		        		<tr>
		        			<td>Show Help</td>
		        			<td class="monospace">H</td>
		        		</tr>
	        		</tbody>
	        	</table>
      		</div>

	      	<div class="modal-footer">
	        	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	      	</div>
    	</div><!-- /.modal-content -->
  	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


{{-- Stop Modal --}}
<div class="modal fade modal-preserve" id="stop-modal">
	<div class="modal-dialog">
    	<div class="modal-content">
      		<div class="modal-header">
        		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        		<h4 class="modal-title">End Test</h4>
      		</div>
      		<div class="modal-body">
				<p class="lead">Are you sure you want to <strong>end this test</strong>?</p>
				<div class="alert alert-danger">
					By checking the box below and clicking the 'I Want To End This Test' button you 
		            acknowledge that you are finished taking this test and that you <strong>cannot</strong> go back.			
				</div>
		        <div class="form-group">
		        	<div class="checkbox">
		        	    <label for="agree_box" class="text-danger">
		        	    	{!! Form::checkbox('agree_box', 'agree', FALSE, ['id' => 'agree_box']) !!} <strong>I understand, stop my test.</strong>
		        	    </label>
		        	</div>        		 
		      	</div>
      		</div>
      		<div class="modal-footer">
        		<button type="button" class="btn btn-danger" data-dismiss="modal">Back to Test</button>
       			<button type="button" class="btn btn-default" id="end-test-confirm">I Want To End This Test</button>
      		</div>
   		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@stop

@section('scripts')
	{!! HTML::script('vendor/jquery.hotkeys/jquery.hotkeys.js') !!}
	{!! HTML::script('vendor/history.js/scripts/bundled/html4+html5/jquery.history.js') !!}
	{!! HTML::script('vendor/hdmaster/core/js/testing/index.js') !!}
@stop