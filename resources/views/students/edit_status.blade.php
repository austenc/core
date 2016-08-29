@extends('core::layouts.default')
	@section('content')
		{!! Form::open(['route' => ['students.status.update', $student->id]]) !!}

		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Edit Status</h1>
				</div>
				<div class="col-xs-4 back-link">
					<a href="{{ route('students.edit', $student->id) }}" class="btn btn-link pull-right">
						{!! Icon::arrow_left() !!} Back to {{ $student->full_name }}
					</a>
				</div>
			</div>

			<div class="well">
				<?php 
                    // status
                    $statArr = explode(',', $student->status);
                    // hold
                    $hold = (in_array('hold', $statArr)) ? 1 : 0;
                    $holdStatus = (in_array('hold', $statArr)) ? 'checked' : '';
                    // lock
                    $locked = (in_array('locked', $statArr)) ? 1 : 0;
                    $lockStatus = (in_array('locked', $statArr)) ? 'checked' : '';
                ?>
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input type="checkbox" id="holdStatus" name="status[]" value="hold" onclick="setHoldView()" <?php echo $holdStatus; ?>> {!! Form::label('status', 'Hold') !!} - <em>Stops sync with outside Agency</em>
						</label>
					</div>
				</div>
				<div class="form-group">
					<div class="checkbox">
						<label>
							<input type="checkbox" id="lockStatus" name="status[]" value="locked" onclick="setLockView()" <?php echo $lockStatus; ?>> {!! Form::label('status', 'Lock') !!} - <em>Prevent login</em>
						</label>
					</div>
				</div>
			</div>

			{{-- Hold --}}
			<div id="divHoldReason" style="display: none;">
				<h3>Hold</h3>
				<div class="well">
					<p>
						@include('core::partials.required') <strong>Instructions</strong> on what the {{ Lang::choice('core::terms.student', 1) }} needs to do to resolve the hold. Shown to the {{ Lang::choice('core::terms.student', 1) }} so please provide detail.
					</p>
					<textarea class="form-control" id="holdinstructions" name="holdinstructions"><?php if (isset($curHold->instructions)) {
    echo $curHold->instructions;
} ?></textarea>

					<hr>

					<p>
						@include('core::partials.required') <strong>Comments</strong> regarding the reason for the hold. Internal purposes only and <strong>NOT</strong> shown to anyone other than Staff.
					</p>
					<textarea class="form-control" id="holdreason" name="holdreason"><?php if (isset($curHold->comments)) {
    echo $curHold->comments;
} ?></textarea>
				</div>
			</div>

			{{-- Lock --}}
			<div id="divLockReason" style="display: none;">
				<h3>Lock</h3>
				<div class="well">
					<p>
						@include("core::partials.required") <strong>Instructions</strong> on how the {{ Lang::choice('core::terms.student', 1) }} may proceed to get the lock removed. Shown when the {{ Lang::choice('core::terms.student', 1) }} attempts to login.
					</p>
					<textarea class="form-control" id="lockinstructions" name="lockinstructions"><?php if (isset($curLock->instructions)) {
    echo $curLock->instructions;
} ?></textarea>
					
					<hr>

					<p>
						@include("core::partials.required") <strong>Comments</strong> regarding the reason for locking this account. Internal purposes only and <strong>NOT</strong> shown to anyone other than Staff.
					</p>
					<textarea class="form-control" id="lockreason" name="lockreason"><?php if (isset($curLock->comments)) {
    echo $curLock->comments;
} ?></textarea>
				</div>
			</div>
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
				<button type="submit" class="btn btn-success">{!! Icon::refresh() !!} Update</button>
			</div>
		</div>

		{!! Form::hidden('clear_hold', 0) !!}
		{!! Form::hidden('clear_lock', 0) !!}
		{!! Form::close() !!}
	@stop
	
	@section('scripts')
		<script language="javascript" type="text/javascript">
			<?php echo "var holdStat = " . $hold . ";\n"; ?>
			<?php echo "var lockStat = " . $locked . ";\n"; ?>
			$(document).ready(function(){
				// Set state of #holdStatus based on checkbox being checked
				if($("#holdStatus").is(':checked')){
					$('#divHoldReason').show();
				} else {
					$('#divHoldReason').hide();
				}

				// Set state of #lockStatus based on checkbox being checked
				if($("#lockStatus").is(':checked')){
					$("#divLockReason").show();
				} else {
					$("#divLockReason").hide();
				}
			})
			function setHoldView(){
				if($("#holdStatus").is(':checked')){
					$('#divHoldReason').show();
					$('input[name=clear_hold]').val(0);
				} else {
					if(holdStat){
						if(confirm("Unchecking Hold will clear the current hold placed on the student.\n\nAre you sure?")){
							$('#divHoldReason').hide();
							$('input[name=clear_hold]').val(1);
						} else {
							$('#holdStatus').prop('checked', true);
						}
					} else {
						$('#divHoldReason').hide();
					}
				}
					
			}
			function setLockView(){
				if($("#lockStatus").is(':checked')){
					$("#divLockReason").show();
					$('input[name=clear_lock]').val(0);
				} else {
					if(lockStat){
						if(confirm("Unchecking Lock will clear the current lock placed on the students account.\n\nAre you sure?")){
							$('#divLockReason').hide();
							$('input[name=clear_lock').val(1);
						} else {
							$('#lockStatus').prop('checked', true);
						}
					} else {
						$('#divLockReason').hide();
					}
				}
			}
		</script>
	@stop