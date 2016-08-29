@extends('core::layouts.default')

@section('content')
	<div class="back-link">
		<a href="javascript:history.back();" class="btn btn-link pull-right">
			{!! Icon::arrow_left() !!}
			Back
		</a>
	</div>
	<h2>
		Directions to {{ $f->name }}
		<small>{{ $f->fullAddress }}</small>
	</h2>
	<br>
	
	{!! Form::open() !!}

	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				{!! Form::label('from', 'From') !!}
				<div class="input-group">
					{!! Form::text('from', null, ['placeholder' => 'Enter Your Location']) !!}
					<div class="input-group-btn">
						<button class="btn btn-success" id="go">Go!</button>						
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				{!! Form::label('to', 'To') !!}
				{!! Form::text('to', $f->fullAddress, ['disabled' => 'disabled']) !!}
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-8">
			<h4>Map</h4>
			<div id="map" class="fluid-map"></div>
		</div>
		<div class="col-md-4">
			<h4>Directions</h4>
			<div class="directions-panel">
				<span class="placeholder">Enter a location to see directions</span>
			</div>
		</div>
	</div>

	{!! Form::close() !!}
@stop

@section('scripts')
	{{-- Pull in google maps + gmaps.js --}}
	@include('core::partials.gmaps')

	<script type="text/javascript">
		
		var directionsDisplay;
		var directionsService = new google.maps.DirectionsService();

		var gmap; // for gmaps instance
		var coords;

		$(document).ready(function(){
			var $to = $('#to');
			var $from = $('#from');

			// Initialize the map
			initializeMap();

			// When the 'Go' button is clicked
			$('#go').click(function() {
				// do some simple validation to make sure there's a 'from' address
				if(validate($from, 'From'))
				{
					// valid to / from values, try to grab directions

					// is the box 'My Location'?
					if($from.val() == 'My Location')
					{
						myLocation();
					}
					else
					{
						calcRoute($from.val(), $to.val());
					}
				}

				// so the form doesn't submit
				return false;
			});
		});

		function initializeMap()
		{
			// geocode to get facility's lat/lng from address and init map
			geocode($('#to').val(), function(latlng) {

				// initialize map
				gmap = new GMaps({
					el: '#map',
					lat: latlng.lat(),
					lng: latlng.lng()
				});				

				// Add a marker on the map for this facility
				gmap.addMarker({
					lat: latlng.lat(),
					lng: latlng.lng(),
					title: "{{ $f->name }}",
					infoWindow: {
					  content: '<p>{{ $f->name }}</p>'
					}
				});				

				// Initialize directions service
				directionsDisplay = new google.maps.DirectionsRenderer();
				directionsDisplay.setMap(gmap.map);
				directionsDisplay.setPanel($('.directions-panel')[0]);

				// Try to grab the user's location and automatically show directions
				myLocation();
			});
		}

		// Try to Geocode address for Lat / Lng values
		function geocode(address, callback)
		{
		    return GMaps.geocode({
			    address: address,
			    callback: function(results, status) {
			      if (status == 'OK') {
			        var result = results[0].geometry.location;
			        if(callback != undefined)
			        {
			           callback(result);
			        }
		    	  }
		  		}
			});
		}

		function myLocation()
		{
			GMaps.geolocate({
			  success: function(position) {
			  	// Set text in 'from' field
			  	$('#from').val('My Location');

			  	// Make a lat/lng object
			  	var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

			  	// Pass this in to our calcRoute function so it will update the map
			  	calcRoute(latlng, $(to).val());
			  },
			  error: function(error) {
			  	flash('Your location could not be found', 'danger');
			  },
			  not_supported: function() {
			    alert("Your browser does not support geolocation");
			  },
			  always: function() {
			    // do nothing
			  }
			});
		}

		function validate(el, label)
		{
			if( ! el.val())
			{
				// Add 'error' validation state
				el.parents('.form-group').addClass('has-error');
				// Flash a message
				flash("You must provide a valid '"+ label +"' address.", "danger");

				return false;
			}
			else
			{
				// remove any validation states
				el.parents('.form-group').removeClass('has-error');

				return true;
			}
		}

		function calcRoute(start, end) {
		    var request = {
		      origin: start,
		      destination: end,
		      travelMode: google.maps.DirectionsTravelMode.DRIVING
		    };
		    var $placeholder = $('.directions-panel .placeholder');
		    var $adp         = $('.adp');

		    directionsService.route(request, function(response, status) {
		      if (status == google.maps.DirectionsStatus.OK) {

		        directionsDisplay.setDirections(response);
		        $placeholder.hide();
		        $adp.show();
		      }
		      else
		      {
		        $placeholder.html('Could not calculate route. Please check your address and contact us if you need help.').show();
		        $adp.hide();
		      }
		    });
		}
	</script>
@stop