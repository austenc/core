@extends('core::layouts.default')

@section('content')
	{!! Form::model($observer, ['route' => ['observers.update', $observer->id], 'method' => 'PUT']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>{{ $observer->fullname }} <small>{{ Lang::choice('core::terms.observer', 1) }}</small></h1>
			</div>
			{!! HTML::backlink('observers.index') !!}
		</div>

		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active">
				<a href="#observer-info" aria-controls="observer info" role="tab" data-toggle="tab">
					{!! Icon::info_sign() !!} {{ Lang::choice('core::terms.observer', 1) }} Info
				</a>
			</li>
			
			{{-- Disciplines --}}
			@foreach($observer->disciplines as $discipline)
				<li role="presentation">
					<a href="#observer-discipline-{{{ strtolower($discipline->abbrev) }}}" aria-controls="disciplines" role="tab" data-toggle="tab">
						{!! Icon::briefcase() !!} {{ $discipline->name }}
					</a>
				</li>
			@endforeach
		</ul>
		<div class="tab-content well">
		    <div role="tabpanel" class="tab-pane active" id="observer-info">
		    	{{-- Warnings --}}
				@include('core::warnings.active_lock', ['lock' => $observer->isLocked])
				@include('core::warnings.active_hold', ['hold' => $observer->isHold])
				@include('core::warnings.multiple_roles', ['user' => $observer->user])
				@include('core::warnings.fake_email', ['user' => $observer->user])
				@include('core::warnings.no_disciplines', ['record' => $observer])
		
				{{-- Identification --}}
				@include('core::observers.partials.identification')

				{{-- Payable Rate --}}
				@include('core::observers.partials.payable_rate', ['payableRates' => $payableRates, 'observer' => $observer])

				{{-- Status --}}
				@include('core::partials.record_status', ['record' => $observer])

				{{-- Contact --}}
				@include('core::partials.contact', ['name' => 'observer', 'record' => $observer])
				
				{{-- Address --}}
				@include('core::partials.address')
		
				{{-- Other Roles --}}
				@include('core::users.other_roles', ['user' => $observer->user, 'ignore' => 'Observer'])
				
				{{-- Login Info --}}
				@include('core::partials.login_info', ['record' => $observer, 'name' => 'observers'])

				{{-- Timestamps --}}
				@include('core::partials.timestamps', ['record' => $observer])

				{{-- Comments --}}
				@include('core::partials.comments', ['record' => $observer])
			</div>

			{{-- Disciplines --}}
			@foreach($observer->disciplines as $discipline)
				<div role="tabpanel" class="tab-pane" id="observer-discipline-{{{ strtolower($discipline->abbrev) }}}">
					@include('core::testteam.partials.disciplines', [
						'record'     => $observer,
						'discipline' => $discipline, 
						'events'     => $disciplineInfo[$discipline->id]['events'],
						'facilities' => $disciplineInfo[$discipline->id]['facilities']
					])
				</div>
			@endforeach
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::observers.sidebars.edit', ['user' => $observer->user])
	</div>
	{!! Form::close() !!}
	{!! HTML::modal('add-discipline') !!}
@stop