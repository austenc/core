<div class="row">
	<div class="col-xs-8">
		<h3 id="facility-{{{ strtolower($discipline->abbrev) }}}-title">
			{{ $discipline->name }} 
			@if( ! $discipline->pivot->active)
				<small>(disabled)</small>
			@endif
		</h3>
	</div>
	<div class="col-xs-4 valign-heading">
		@if($discipline->pivot->active && ! Form::isDisabled() && Auth::user()->can('facilities.remove.discipline'))
			<a href="{{ route('facilities.discipline.deactivate', [$facility->id, $discipline->id]) }}" id="remove-disc-{{{ $discipline->id }}}" class="btn btn-sm btn-danger pull-right" data-confirm="Remove {{{ $discipline->name }}} for this {{{ Lang::choice('core::terms.facility', 1) }}}?<br><br>Are you sure?">
				{!! Icon::exclamation_sign() !!} Deactivate
			</a>
		@endif

		{{-- Add Affiliates --}}
		@if( ! Form::isDisabled() && Auth::user()->can('facilities.manage_affiliated'))
			<a href="{{ route('facilities.affiliate.attach', [$facility->id, $discipline->id]) }}" data-toggle="modal" data-target="#add-affiliate" class="btn btn-default btn-sm pull-right" id="attach-affiliate-{{ strtolower($discipline->abbrev) }}-btn">
				{!! Icon::plus_sign() !!} Add Affiliate
			</a>
		@endif
	</div>
</div>
<div class="well">
	<div class="form-group">
		{!! Form::label('discipline_license', 'License') !!}
		{!! Form::text('discipline_license['.$discipline->id.']', $discipline->pivot->tm_license, ['disabled']) !!}
		<span class="text-danger">{{ $errors->first('name') }}</span>
	</div>

	<div class="form-group">
		{!! Form::label('discipline_parent', 'Training Parent') !!}

		{{-- Active Discipline? --}}
		@if($discipline->pivot->active)
			{!! Form::select('discipline_parent['.$discipline->id.']', $avParents[$discipline->id], $discipline->pivot->parent_id) !!}
			<span class="text-danger">{{ $errors->first('license') }}</span>
		@else
			{!! Form::select('discipline_parent['.$discipline->id.']', $avParents[$discipline->id], $discipline->pivot->parent_id, ['disabled']) !!}
			{!! Form::hidden('discipline_parent['.$discipline->id.']', $discipline->pivot->parent_id ?: 0) !!}
		@endif
	</div>

	{{-- Child Programs --}}
	@if( ! empty($childPrograms[$discipline->id]))
		@foreach($childPrograms[$discipline->id] as $i => $child)
			<div class="form-group">
				{!! Form::label('discipline_child', 'Child #'.($i + 1)) !!}
				<div class="input-group">
					{!! Form::text('discipline_child['.$child->id.']', $child->name, ['disabled']) !!}
					<div class="input-group-addon">
						<a href="{{ route('facilities.edit', $child->id) }}">{!! Icon::pencil() !!}</a>
					</div>
				</div>
			</div>
		@endforeach
	@endif

	<hr>

	@include('core::facilities.partials.testing_affiliated', ['discipline' => $discipline, 'affiliates' => $affiliates[$discipline->id]])
</div>