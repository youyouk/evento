(function($) {
	/***************************************************************
	 *** MAP ***
	 **************************************************************/
	if(typeof google != 'undefined') {
		var Map = {
			map: null,
			addressMarker: null,
			country: '',
			options: {
				scrollwheel: false,
				zoom: 15,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				center: new google.maps.LatLng(34.921631, -35.65625, true),
				mapTypeControl: false,
				streetViewControl: true
			},

			// start map
			init: function (container) {
				this.map = new google.maps.Map(document.getElementById(container), this.options);
			},

			// show a marker in the specified latitude and longitude
			showLatLng: function (lat, lng) {
				this.options.center = new google.maps.LatLng(lat, lng);
				if(!this.addressMarker) {
					this.addressMarker = new google.maps.Marker(
						{
							map: this.map,
							position: this.options.center
						});
				}
				else {
					this.addressMarker.setPosition(this.options.center);
				}
				this.map.panTo(this.options.center);
			},

			// show a marker in the specified address
			showAddress: function (address) {
				geocoder = new google.maps.Geocoder();
				geocoder.geocode( { 'address': address}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						Map.map.setCenter(results[0].geometry.location);
						if(!Map.addressMarker) {
							Map.addressMarker = new google.maps.Marker({
								map: Map.map,
								position: results[0].geometry.location
							});
						}
						else {
							Map.addressMarker.setPosition(results[0].geometry.location);
						}
					} else {
						//alert("Geocode was not successful for the following reason: " + status);
					}
				});
			},

			// allow to move the marker in the map
			mapEdit: function () {
				if(this.addressMarker === null) {
					this.addressMarker = new google.maps.Marker({
						map: this.map,
						draggable: true
					});
				}
				else {
					this.addressMarker.setOptions({draggable: true});
				}
				this.options.zoom = 2;
				this.options.streetViewControl = false;
				this.map.setOptions(this.options);

				google.maps.event.addListener(Map.addressMarker, "position_changed",
					function() {
						point = Map.addressMarker.getPosition();
						$("#VenueLat").val(point.lat());
						$("#VenueLng").val(point.lng());
					}
				);
			}
		};
	}

	/***************************************************************
	 *** FUNCTIONS ***
	 **************************************************************/
	// enable or disable an element
	function toggleEnabled(el) {
		if(el.is(':disabled')) el.removeAttr('disabled');
		else el.attr('disabled', 'disabled');
	}

	// enable or disable monthly repeat options
	function swithMonthlyOptions() {
		toggleEnabled($('#EventDirection'))
		toggleEnabled($('#EventMonthDay'));
		toggleEnabled($('#EventMonthlyWeekdays'));
	}

	// enable or disable general repeat options
	function switchGeneralOptions() {
		toggleEnabled($('#EventRepeatOccurrences'));
		toggleEnabled($('#EventRepeatUntilDay'));
		toggleEnabled($('#EventRepeatUntilMonth'));
		toggleEnabled($('#EventRepeatUntilYear'));
		toggleEnabled($('#EventRepeatUntilHour'));
		toggleEnabled($('#EventRepeatUntilMin'));
		toggleEnabled($('#EventRepeatUntilMeridian'));
	}

	// switch the end date checkbox
	var $end_date_input = $('#end_date_input').hide();
	var $start_date_input = $('#start_date_input');
	function switchEndDate(reset) {
		var elements = $('.EndDate');
		if($('#end_date_check').is(':checked')) {
			elements.each(function(i, el)  { $(el).removeAttr('disabled'); });
			if (reset != false) {
				$end_date_input.find('#EventEndDateDay').val($start_date_input.find('#EventStartDateDay').val());
			  $end_date_input.find('#EventEndDateMonth').val($start_date_input.find('#EventStartDateMonth').val());
			  $end_date_input.find('#EventEndDateYear').val($start_date_input.find('#EventStartDateYear').val());
			  $end_date_input.find('#EventEndDateHour').val($start_date_input.find('#EventStartDateHour').val());
			  $end_date_input.find('#EventEndDateMin').val($start_date_input.find('#EventStartDateMin').val());
			  $end_date_input.find('#EventEndDateMeridian').val($start_date_input.find('#EventStartDateMeridian').val());
			}
			$end_date_input.show();
		}
		else {
			elements.each(function(i, el)  { $(el).attr('disabled', 'disabled'); });
			$end_date_input.hide();
		}
	}

	// hide or show the event repeat options blocks
	function hideFields(option) {
		var repeat_options = $('#repeat-options').hide();
		var repeat_daily = $('#repeat-daily-form').hide();
		var repeat_weekly = $('#repeat-weekly-form').hide();
		var repeat_monthly = $('#repeat-monthly-form').hide();
		switch(option) {
			case 'daily':
				repeat_daily.show();
				break;
			case 'weekly':
				repeat_weekly.show();
				break;
			case 'monthly':
				repeat_monthly.show();
				break;
		}
		if(option!='does_not_repeat') {
			repeat_options.show();
		}
	};

	/***************************************************************
	 *** DOCUMENT READY ***
	 **************************************************************/
	$(document).ready(function() {

    /**
     * write a comment
     */
    $('#CommentWrite').live('submit', function(e) {
      e.preventDefault();
      $this = $(e.currentTarget);
      $this.find('#submit-button').addClass('disabled-button').attr('disabled', 'disabled');
      $.post(
        $this.attr('action'),
        $this.serialize(),
        function(data, status, xhr) {
          $('.comments-form').remove();
          $('.block-comments').append(data);
        }, 'html'
      )
      .fail(function() {
        window.location.reload(true);
      });
    });

    var thumbsPaginator = $('#thumbnails-paginator');
    if(thumbsPaginator.length) {
      var thumbsNext = thumbsPaginator.find('#thumbs-next');
      var thumbsPrev = thumbsPaginator.find('#thumbs-prev');

      var paginate = function(e) {
        e.preventDefault();
        $.get(
          $(e.currentTarget).attr('href'),
          null,
          function(data, textStatus, jqXHR) {
            $('#thumbnails-column').replaceWith(data);
          }
        );
      };
          
      $('#thumbs-next').live('click', paginate);
      $('#thumbs-prev').live('click', paginate);

    }

    // image gallery in events view page
    $('.small-image').live('click', function(e) {
      e.preventDefault();
      $('#photo-loader').show();
      var $this = $(e.currentTarget);
      var pImg = new Image();
      pImg.src = $this.data('photo');
      pImg.onload = function() {
        $('#photo-loader').hide();
        $('#big-image').attr('src', $this.data('photo'));
      }
    });


		// start google map
		var map_container = $('div#map');
		if(map_container.length) {
			Map.init('map');
			if(map_container.data('lat')) {
				Map.showLatLng(map_container.data('lat'), map_container.data('lng'));
			}
			if(map_container.hasClass('map-edit')) {
				Map.mapEdit();
			}
		}

		// i'm going button
		var imgoing_container = $('#imgoing');
		if(imgoing_container.length) {
			imgoing_container.on('click', function(e) {
				$.ajax({
					url: imgoing_container.attr('href'),
					dataType: 'json',
					success: function(data) {
						imgoing_container.text(data.text);
						imgoing_container.attr('href', data.link);
						if(data.attendee == 0) {
							imgoing_container.addClass('imgoing-button');
							imgoing_container.removeClass('imgoing-cancel');
							$('#user-list #attendee-me').remove();
							if($('#user-list a').length < 1) {
								$('#user-list').prepend(data.empty);
							}
						}
						else {
							imgoing_container.removeClass('imgoing-button');
							imgoing_container.addClass('imgoing-cancel');
							$('#user-list').prepend(data.template);
							$('#user-list p.empty-message').remove();
						}
						//flashMessage(data.flash);
					},
					error: function(error) {
						//console.log(error);
					}
				});
				e.preventDefault();
			});
		}

		// disable submit button after a form has been submited
		var submit_button = $('#submit-button');
		if(submit_button.length) {
			var form = submit_button.parents('form');
			form.on('submit', function(e) {
				submit_button.attr('disabled', 'disabled');
			});
		}

		/***************************************************************
		 *** VENUES FORM ***
		 **************************************************************/
		var f = $('form.event-form');
		var v = $('form.venue-form');

		if(f.length || v.length) {

			var venue_block = $("#venue-name-block");
			if(venue_block.length) {venue_block.hide();}

			// reset venue link
			$('#reset-venue').on('click', function (e) {
				$("#EventVenueId").val('');
				$("#VenueName").val('');
				$("#VenueAddress").val('');
				$("#VenueLat").val('');
				$("#VenueLng").val('');
				$("#venue-name-block").toggle();
				$("#VenueName").toggle();
				$("#VenueName").focus();
				e.preventDefault();
			});

			// when a country is selected show it in the map
			var country_name = $('#CityCountryName');
			var country_id = $('#CityCountryId');
			var city_name = $('#CityName');

			country_id.on('change',
				function(e) {
					Map.country = country_id.find('option:selected').text();
					Map.map.setZoom(4);
					Map.showAddress(Map.country);
				})
				.find('option:selected').text();

			// handle autocomplete fields
			$('.auto_complete').prev('input').on('keyup', function(e) {
				var $this = $(this);
				var autocomplete = $this.next('div');
				if($this.val().length > 0 && e.keyCode !== 27) {
					var data = {};
					data[$this.attr('name')] = $this.val();
					if(autocomplete.data('rfield') == 'country' && country_id.length) {
						data[country_id.attr('name')] = country_id.find('option:selected').val();
					}
					$.ajax({
						url: autocomplete.data('url'),
						type: 'POST',
						data: data,
						success: function(data) {
							if($(data).children().length > 0) {
								autocomplete.css('top', autocomplete.prev('input').css('bottom'));
								autocomplete.html(data);
								autocomplete.fadeIn();
							}
							else {
								autocomplete.fadeOut();
							}
						}
					});
				}
				else {
					autocomplete.hide();
				}
			})
			.blur(function() {
				$('.auto_complete').delay(500).fadeOut();
			});

			// click event on the venues autocomplete list
			$('#VenueName_autoComplete ul li').live('click', function(e) {
				var $this = $(this)
				if($this.children().eq(0).attr('id')==0) {
					$("#venue-form").show();
					if(typeof google != 'undefined') {
						google.maps.event.trigger(Map.map, 'resize');
						Map.map.setOptions(Map.options);

						if(country_name.val()) {
							Map.country = country_name.val();
							if(city_name.val()) {
									Map.map.setZoom(15);
									Map.showAddress(city_name.val() + ', ' + Map.country);
							} else {
								Map.map.setZoom(4);
								Map.showAddress(Map.country);
							}
						}

					}
				}
				else {
					$("#VenueName").val($this.children().eq(0).text());
					$("#EventVenueId").val($this.children().eq(0).attr('id'));
					$("#venue-data").html($this.children());
					$("#VenueName").hide();
					$("#venue-name-block").show();
					$("#venue-form").hide();
				}
				$('#VenueName_autoComplete').hide();
			});

			// click a city in the cities autocomplete list
			$('#CityName_autoComplete ul li').live('click', function() {
				$('#CityName').val($(this).text()).trigger('change');
				$('#CityName_autoComplete').hide();
			});

			// update the map marker address
			$('#CityName').on("change keyup", function() {
				Map.showAddress($(this).val() + ', ' + Map.country);
			});

			// show address in the map as the user types it
			$("#VenueAddress").on('keyup', function() {
				Map.map.setZoom(15);
				Map.showAddress($("#VenueAddress").val() + ", " + $("#CityName").val() + ", " + Map.country);
			});
		}

		if($("#VenueAddress").val()) {
			Map.map.setZoom(15);
			Map.showAddress($("#VenueAddress").val() + ", " + $("#CityName").val() + ", " + Map.country);
		}

		/***************************************************************
		 *** EVENTS FORM ***
		 **************************************************************/
		if(f.length) {
			// if venue data exists show the venue box
			var event_venue_id = $('#EventVenueId');
			if(event_venue_id.length && event_venue_id.val()) {
				var element = "<p class=\"venue-name\">"
				+ $('#hiddenVenueName').val() + '</p><p class=\"venue-info\">'
				+ $('#hiddenVenueAddress').val() + ', '
				+ $('#hiddenCityName').val() + ', '
				+ $("#hiddenCountryName").val() + '.</p>';

				$("#venue-data").append(element);
				$("#venue-name-block").toggle();
				$("#VenueName").toggle();
			}

			// click an option in the tags autocomplete list
			var tags = $('#Tags');
			$('#Tags_autoComplete ul li').live('click', function(e) {
				var item = $(this).text();
				if(tags.val().lastIndexOf(",")>0) item = ', ' + item;
				tags.val(tags.val().substr(0, tags.val().lastIndexOf(',')) + item);
				tags.focus();
				$(this).closest('div.auto_complete').hide();
			});

			// enable or disable the end date fields
			var end_date_field = $('#end_date_check');
			if(end_date_field.is(':checked')) switchEndDate(false);
			end_date_field.on('change', function(e) {
				switchEndDate();
			});

			/***************************************************************
			 *** EVENT REPEAT OPTIONS ***
			 **************************************************************/
			// show the corresponding panel when repeat select input changes
			var event_repeat = $('#EventRepeat');
			event_repeat.on('change', function(e) {
				hideFields(event_repeat.find('option:selected').val());
			});
			event_repeat.trigger('change');

			// disable specific day fields in monthly repeat
			$('#EventDirection').attr('disabled', 'disabled');
			$('#EventMonthDay').attr('disabled', 'disabled');
			$('#EventRepeatOccurrences').attr('disabled', 'disabled');

			// disable or enable corresponding fields in monthly repeat
			$('#same_day').on('change', function(e) {
				swithMonthlyOptions();
			});

			// disable or enable corresponding fields in monthly repeat
			$('#specific_day').on('change', function(e) {
				swithMonthlyOptions();
			});

			// disable or enable corresponding repeat fields
			$('#repeat_occurences').on('change', function(e) {
				switchGeneralOptions();
			});

			// disable or enable corresponding repeat fields
			$('#repeat_until').on('change', function(e) {
				switchGeneralOptions();
			});
		}
	});
})(jQuery);