@extends('core::layouts.default')

@section('content')
	{!! Form::model($proctor, ['route' => ['proctors.update', $proctor->id], 'method' => 'PUT']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>{{ $proctor->fullname }} <small>{{ Lang::choice('core::terms.proctor', 1) }}</small></h1>
			</div>
			{!! HTML::backlink('proctors.index') !!}
		</div>

		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active">
				<a href="#proctor-info" aria-controls="proctor info" role="tab" data-toggle="tab">
					{!! Icon::info_sign() !!} {{ Lang::choice('core::terms.proctor', 1) }} Info
				</a>
			</li>

			{{-- Disciplines --}}
			@foreach($proctor->disciplines as $discipline)
				<li role="presentation">
					<a href="#proctor-discipline-{{{ strtolower($discipline->abbrev) }}}" aria-controls="disciplines" role="tab" data-toggle="tab">
						{!! Icon::briefcase() !!} {{ $discipline->name }}
					</a>
				</li>
			@endforeach
		</ul>
		<div class="tab-content well">
		    <div role="tabpanel" class="tab-pane active" id="proctor-info">
		    	{{-- Warnings --}}
				@include('core::warnings.active_hold', ['hold' => $proctor->isHold])
				@include('core::warnings.active_lock', ['lock' => $proctor->isLocked])
				@include('core::warnings.multiple_roles', ['user' => $proctor->user])
				@include('core::warnings.fake_email', ['user' => $proctor->user])
				@include('core::warnings.no_disciplines', ['record' => $proctor])
		
				{{-- Identification --}}
				@include('core::proctors.partials.identification')

				{{-- Status --}}
				@include('core::partials.record_status', ['record' => $proctor])

				{{-- Contact --}}
				@include('core::partials.contact', ['name' => 'proctor', 'record' => $proctor])
				
				{{-- Address --}}
				@include('core::partials.address')
		
				{{-- Other Roles --}}
				@include('core::users.other_roles', ['user' => $proctor->user, 'ignore' => 'Proctor'])

				{{-- Login Info --}}
				@include('core::partials.login_info', ['record' => $proctor, 'name' => 'proctors'])

				{{-- Timestamps --}}
				@include('core::partials.timestamps', ['record' => $proctor])

				{{-- Comments --}}
				@include('core::partials.comments', ['record' => $proctor])
			</div>

			{{-- Disciplines --}}
			@foreach($proctor->disciplines as $discipline)
				<div role="tabpanel" class="tab-pane" id="proctor-discipline-{{{ strtolower($discipline->abbrev) }}}">
					@include('core::testteam.partials.disciplines', [
						'record'     => $proctor,
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
		@include('core::proctors.sidebars.edit', ['user' => $proctor->user])
	</div>
	{!! Form::close() !!}
	{!! HTML::modal('add-discipline') !!}
@stop