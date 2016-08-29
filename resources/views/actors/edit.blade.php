@extends('core::layouts.default')

@section('content')
	{!! Form::model($actor, ['route' => ['actors.update', $actor->id], 'method' => 'PUT']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>{{ $actor->fullname }} <small>{{ Lang::choice('core::terms.actor', 1) }}</small></h1>
			</div>
			{!! HTML::backlink('actors.index') !!}
		</div>

		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active">
				<a href="#actor-info" aria-controls="actor info" role="tab" data-toggle="tab">
					{!! Icon::info_sign() !!} {{ Lang::choice('core::terms.actor', 1) }} Info
				</a>
			</li>

			{{-- Disciplines --}}
			@foreach($actor->disciplines as $discipline)
				<li role="presentation">
					<a href="#actor-discipline-{{ strtolower($discipline->abbrev) }}" aria-controls="disciplines" role="tab" data-toggle="tab">
						{!! Icon::briefcase() !!} {{ $discipline->name }}
					</a>
				</li>
			@endforeach
		</ul>
		<div class="tab-content well">
			<!-- Actor Info -->
		    <div role="tabpanel" class="tab-pane active" id="actor-info">
		    	{{-- Warnings --}}
				@include('core::warnings.active_hold', ['hold' => $actor->isHold])
				@include('core::warnings.active_lock', ['lock' => $actor->isLocked])
				@include('core::warnings.multiple_roles', ['user' => $actor->user])
				@include('core::warnings.fake_email', ['user' => $actor->user])
				@include('core::warnings.no_disciplines', ['record' => $actor])
		
				{{-- Identification --}}
				@include('core::actors.partials.identification')

				{{-- Status --}}
				@include('core::partials.record_status', ['record' => $actor])

				{{-- Contact --}}
				@include('core::partials.contact', ['name' => 'actor', 'record' => $actor])
				
				{{-- Address --}}
				@include('core::partials.address')

				{{-- Other Roles --}}
				@include('core::users.other_roles', ['user' => $actor->user, 'ignore' => 'Actor'])

				{{-- Login Info --}}
				@include('core::partials.login_info', ['record' => $actor, 'name' => 'actors'])

				{{-- Timestamps --}}
				@include('core::partials.timestamps', ['record' => $actor])

				{{-- Comments --}}
				@include('core::partials.comments', ['record' => $actor])
			</div>

			{{-- Disciplines --}}
			@foreach($actor->disciplines as $discipline)
				<div role="tabpanel" class="tab-pane" id="actor-discipline-{{ strtolower($discipline->abbrev) }}">
					@include('core::testteam.partials.disciplines', [
						'record'     => $actor,
						'discipline' => $discipline, 
						'events'     => $disciplineInfo[$discipline->id]['events'],
						'facilities' => $disciplineInfo[$discipline->id]['facilities']
					])
				</div>
			@endforeach
		</div>
	</div> {{-- .col-md-9 --}}
	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::actors.sidebars.edit', ['user' => $actor->user])
	</div>
	{!! Form::close() !!}
	{!! HTML::modal('add-discipline') !!}
@stop