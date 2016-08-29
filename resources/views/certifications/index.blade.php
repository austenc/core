@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'certifications.index', 'method' => 'get']) !!}
	<div class="row">
		<div class="col-md-9">
			<p class="lead">Certifications</p>
			<div class="form-group">
				{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
				<div class="input-group">
					{!! Form::text('search', Input::get('search'), 
					['placeholder' => 'Search for Certifications', 'autofocus' => 'autofocus']) !!}
					<span class="input-group-btn">
						<button class="btn btn-info" type="submit">
							{!! Icon::search() !!} <span class="sr-only">Search</span>
						</button>
      				</span>
      			</div>
      		</div>

			<div class="well table-responsive">
	      		<table class="table table-striped">
					<thead>
						<tr>
							<th>{!! Sorter::link('certifications.index', 'Name', ['sort' => 'name']) !!}</th>
							<th>Discipline</th>
							<th>Requirements</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($certs as $cert)
							<tr>
								<td>
									<a href="{{ route('certifications.edit', $cert->id) }}">
										{{ $cert->name }}
									</a><br>
									<small>{{ $cert->abbrev }}</small>
								</td>

								<td>{{ $cert->discipline->name }}</td>

								<td>
									@foreach($cert->required_exams as $reqExam)
										<div>{{ $reqExam->pretty_name }}</div>
									@endforeach

									@foreach($cert->required_skills as $reqSkill)
										<div>{{ $reqSkill->pretty_name }}</div>
									@endforeach
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
				{!! $certs->appends(Input::except('page'))->render() !!}
			</div>
		</div>

		{{-- Sidebar --}}
		@include('core::certifications.sidebar')
	</div>
	{!! Form::close() !!}
@stop