/* *
* Author: zetamatic
* @package checkout_address_autofill_for_woocommerce
*/

// for image upload
jQuery(document).ready(function( $ ) {
  if($('.wc_gaa_countries').length) {
    $('.wc_gaa_countries').select2();
  }
  $('.image_logo_upload').click(function(e) {
    e.preventDefault();

    var custom_uploader = wp.media({
      title: 'Location Image',
      button: {
        text: 'Upload Image'
      },
      multiple: false
    })
    .on('select', function() {
      var attachment = custom_uploader.state().get('selection').first().toJSON();
      $('.image_logo').attr('src', attachment.url);
      $('.image_logo_url').val(attachment.url);
    })
    .open();
   });
});

// Getting data from autocomplete field
  var autofill, place;
  function initAutocomplete(){

    if( jQuery('#shipping_autofill_checkout_field').length > 0 ) {

      if( wcaf.autofill_for_shipping ) {
        autofill_for_shipping = new google.maps.places.Autocomplete(document.getElementById('shipping_autofill_checkout_field'), { types: ['address']});

        if( wcaf.selectedCountry.length > 0  && wcaf.selectedCountry !== undefined ) {
          autofill_for_shipping.setComponentRestrictions(
                {'country': wcaf.selectedCountry });
        }
        autofill_for_shipping.addListener('place_changed', function () { fillAddress('shipping'); } );
      }

    }

    autofill = new google.maps.places.Autocomplete(document.getElementById('autofill_checkout_field'));

    if( wcaf.selectedCountry.length > 0  && wcaf.selectedCountry !== undefined ) {
      autofill.setComponentRestrictions(
            {'country': wcaf.selectedCountry });
    }

    autofill.addListener('place_changed', function () { fillAddress('billing'); } );
  }

// Filling the address. Used both for billing and shipping.
// @param field string Either 'shipping' or 'billing' fields section.
	function fillAddress(field){
    place = autofill.getPlace();

		/**
		* Define all possible data to collect from getPlace() API call.
		*
		* Let's use this object to map later on data into each checkout field with
		* logic tailored to each field.
		*
		* See: https://developers.google.com/places/web-service/supported_types
		*/
		var address_data = {
			street_address: '',
			street_number: '',
			sublocality: '',
			route: '',
			neighborhood: '',
			floor: '',
			sublocality_level_1: '',
			sublocality_level_2: '',
			sublocality_level_3: '',
			postal_code: '',
			postal_code_prefix: '',
			postal_town: '',
			locality: '',
			country: '',
			administrative_area_level_1: '',
			administrative_area_level_2: ''
		};

		/**
		 * Helper arrays to extract data from API response. The value can came
		 * in 'long_name' or 'short_name' object properties in the object from
		 * API's response. This arrays are used to extract the right value form
		 * the API's response.
		 */
		var components_short_name = [
			'country',
			'administrative_area_level_1',
			'administrative_area_level_2'
		];
		var components_long_name = [
			'street_number',
			'sublocality',
			'route',
			'neighborhood',
			'sublocality_level_1',
			'sublocality_level_3',
			'sublocality_level_2',
			'locality',
			'postal_code',
			'postal_code_prefix',
			'postal_town',
			'street_address',
			'floor'
		];

		// Extract data from API response into address_data object.
    for (var i = 0; i < place.address_components.length; i++) {
      var addressType = place.address_components[i].types[0];

			// Components we use 'short_name'.
			if ( components_short_name.includes(addressType) ) {
				address_data[addressType] = place.address_components[i]['short_name'];
			}

			// Components we use 'long_name'.
			if ( components_long_name.includes(addressType) ) {
				address_data[addressType] = place.address_components[i]['long_name'];
			}
		}
		// If not filled in type 0, may try with the type 1.
    for (var i = 0; i < place.address_components.length; i++) {
			try {
				// May not exists ...types[1]
				var addressType = place.address_components[i].types[1];
			} catch(err) {
				continue;
			}
			if (address_data[addressType] !== '') {
				// Case already have data for this addressType.
				continue;
			}

			// Components we use 'short_name'.
			if ( components_short_name.includes(addressType) ) {
				address_data[addressType] = place.address_components[i]['short_name'];
			}

			// Components we use 'long_name'.
			if ( components_long_name.includes(addressType) ) {
				address_data[addressType] = place.address_components[i]['long_name'];
			}
		}

		// filling state field after the country is set and field updated.
		jQuery( document.body ).on( 'country_to_state_changed', function( e, country, $wrapper) {
			var state = address_data['administrative_area_level_1'];
			jQuery('#' + field + '_state').val(state);
			jQuery('#' + field + '_state').trigger('change');

			// Administrative area level 1 may not match the state values for some countries.
			var state = address_data['administrative_area_level_2'];

			// State is options selector and the option is there with same name in it's value.
			if (jQuery('#' + field + '_state option[value="' + state + '"]').length != 0){
				state = jQuery('#billing_state option[value="' + state + '"]').text();
				jQuery('#' + field + '_state').val(state);
				jQuery('#' + field + '_state').trigger('change');
			}

			// State may be options selector but the option have the state text in the label, not the value.
			else if(jQuery('#' + field + '_state').val() == '' ||jQuery('#' + field + '_state').val() == null ||jQuery('#' + field + '_state option[value="' + state + '"]').length == 0) {
				// If no value for the state, search for the text in the option label.
				const adm_lvl_2_state = state;
				jQuery('#' + field + '_state').ready(function() {
					jQuery('#' + field + '_state option').each(function ( index ) {
						state = adm_lvl_2_state;
						if (jQuery(this).text() == state) {
							state = jQuery(this).val();
							jQuery('#' + field + '_state').val(state);
							jQuery('#' + field + '_state').trigger('change');
							return; // stop .each iteration.
						}
					});
				})
			}
			
			// State field may be a text field. In such case it's going to be empty after all this process.
			if(jQuery('#' + field + '_state').val() == '' ||jQuery('#' + field + '_state').val() == null ) {
				jQuery('#' + field + '_state').val(state);
				jQuery('#' + field + '_state').trigger('change');
			}
			// We add extra information to state in case is a free text field.
			else if (jQuery('#' + field + '_state').attr('type') === 'text') {
				jQuery('#' + field + '_state').val( jQuery('#' + field + '_state').val() + ', ' + state );
				jQuery('#' + field + '_state').trigger('change');
			}
		});

		/**
		 * Process billing fields
		 */
    jQuery('#' + field + '_address_1').val('');
		var address_1 = address_data['street_address'] + ' ' + address_data['floor'] + ' ' + address_data['route'] + ' ' + address_data['street_number'] + ' ' + address_data['neighborhood'];
		jQuery('#' + field + '_address_1').val(address_1.replace( /\s\s+/g, ' ' ).trim());

    jQuery('#' + field + '_address_2').val('');
		var address_2 = address_data['sublocality'] + ' ' + address_data['sublocality_level_1'] + ' ' + address_data['sublocality_level_2'] + ' ' + address_data['sublocality_level_3'];
		jQuery('#' + field + '_address_2').val(address_2.replace( /\s\s+/g, ' ' ).trim());

    jQuery('#' + field + '_postcode').val('');
		if ( address_data['postal_code'] !== '' ) {
			jQuery('#' + field + '_postcode').val(address_data['postal_code']);
		} else if ( address_data['postal_code_prefix'] !== '' ) {
			jQuery('#' + field + '_postcode').val(address_data['postal_code_prefix']);
		}

		if ( address_data['locality'] !== '' ) {
			jQuery('#' + field + '_city').val(address_data['locality']);
		} else if ( address_data['postal_town'] !== '' ) {
			// Cases like 'London' came into 'postal_town' in the API's response.
			jQuery('#' + field + '_city').val(address_data['postal_town']);
		}

		jQuery('#' + field + '_country').val(address_data['country']);
		jQuery('#' + field + '_country').trigger('change');

    jQuery('#' + field + '_phone').val('');
    jQuery('#' + field + '_company').val('');

    if( field === 'billing' && wcaf.enable_billing_phone ) {
      if( place.international_phone_number ){
        jQuery('#billing_phone').val(place.international_phone_number);
				jQuery('#billing_phone').trigger('change');
      }
    }

    if( field === 'billing' && wcaf.enable_billing_company_name ) {
      if( place.name && ! jQuery('#' + field + '_address_1').val().includes(place.name) ){
				// Note that place.name may contain redundant information with address_1 field.
        jQuery('#billing_company').val(place.name);
				jQuery('#billing_company').trigger('change');
      }
    } else if( field === 'shipping' && wcaf.enable_shipping_company_name ) {
      if( place.name ) {
        jQuery('#shipping_company').val(place.name);
      }
    }

		// Triggers woocommerce field validations.
		if ( jQuery('#' + field + '_postcode').is(":visible") ) {
		   jQuery('#' + field + '_postcode').trigger('change');
	  }
		if ( jQuery('#' + field + '_address_2').is(":visible") ) {
		   jQuery('#' + field + '_address_2').trigger('change');
	  }
		if ( jQuery('#' + field + '_address_1').is(":visible") ) {
		   jQuery('#' + field + '_address_1').trigger('change');
	  }
		if ( jQuery('#' + field + '_city').is(":visible") ) {
		   jQuery('#' + field + '_city').trigger('change');
	  }
		if ( jQuery('#' + field + '_state').is(":visible") ) {
		   jQuery('#' + field + '_state').trigger('change');
	  }
    
		// Trigger event when fields are ready for 3rd party hooking.
		jQuery(window).trigger('wcaf_post_fill_' + field + '_address');
  }

// Getting for geolocation support
  function shipping_geolocate() {
    if(navigator.geolocation) {
      navigator.geolocation.getCurrentPosition( shipping_geoSuccess, geoError );
    } else {
      alert("Geolocation is not supported by this browser.");
    }
  }

  function billing_geolocate() {
    if(navigator.geolocation) {
      navigator.geolocation.getCurrentPosition( billing_geoSuccess, geoError );
    } else {
      alert("Geolocation is not supported by this browser.");
    }
  }


// Funtion for error
  function geoError() {
    console.log("Geocoder failed.");
  }

// Function for success and getting coordinates
  function billing_geoSuccess(position) {
    var lat = position.coords.latitude;
    var lng = position.coords.longitude;
    billing_codeLatLng(lat, lng);
  }

  // Function for success and getting coordinates
  function shipping_geoSuccess(position) {
    var lat = position.coords.latitude;
    var lng = position.coords.longitude;
    shipping_codeLatLng(lat, lng);
  }


// Function to fill address
  var ship_geocoder ;
  function shipping_codeLatLng( lat, lng ) {
    ship_geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(lat, lng);
    ship_geocoder.geocode({'latLng': latlng}, function( results, status ) {

      if( status == google.maps.GeocoderStatus.OK ) {
        if( results[0] ) {
          var address = results[0].address_components;

          jQuery('#shipping_postcode').val('');
          jQuery('#shipping_address_2').val('');
          jQuery('#shipping_city').val('');
          // jQuery('#shipping_company').val('');

          for (var i = 0; i < address.length; i++) {
            var addressType = address[i].types[0];


            // filling country field
            if(addressType == 'country'){
              jQuery('#shipping_country').val(address[i]['short_name']);
              jQuery('#shipping_country').trigger('change');
            }
            // filling street address
            if(addressType == 'street_number' ){
              jQuery('#shipping_address_1').val(address[i]['long_name']);
            } else {
              if( typeof ( address[i].types[1] != "undefined" ) ) {
                if( address[i].types[1] == 'sublocality' ) {
                  jQuery('#shipping_address_1').val(address[i]['long_name']);
                }
              }
            }
            // adding data to street address
            if(addressType == 'route'){
              var addr = jQuery('#shipping_address_1').val();
              if(addr != ''){
                addr = addr +' '+ address[i]['long_name'];
                jQuery('#shipping_address_1').val(addr);
              }
            }

            // filling state field
            if(addressType == 'administrative_area_level_1'){
              var state = address[i]['short_name'];
              setTimeout(function(){
                jQuery('#shipping_state').val(state);
                jQuery('#shipping_state').trigger('change');
              },1500);
            }

            if(addressType == 'neighborhood'){
              jQuery('#shipping_address_2').val(address[i]['long_name']);
            } else if(addressType == 'sublocality_level_3'){
              jQuery('#shipping_address_2').val(address[i]['long_name']);
            } else if(addressType == 'sublocality_level_2'){
              jQuery('#shipping_address_2').val(address[i]['long_name']);
            }

            // filling location
            if(addressType == 'locality'){
              jQuery('#shipping_city').val(address[i]['long_name']);
            }
            // filling postal code field
            if(addressType == 'postal_code'){
              jQuery('#shipping_postcode').val(address[i]['long_name']);
            }
          }

          if (wcaf.enable_shipping_company_name) {
            if (results[0].hasOwnProperty("name") && results[0].name) {
              jQuery('#shipping_company').val(results[0].name);
            }
          }

        }
        else {
          alert("No results found");// alerting if no results found
        }
      }
      else {
        console.log("Geocoder failed due to: " + status);
      }
  });
}



// Function to fill address
  var geocoder ;
  function billing_codeLatLng( lat, lng ) {
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(lat, lng);
    geocoder.geocode({'latLng': latlng}, function( results, status ) {

      if( status == google.maps.GeocoderStatus.OK ) {
        if( results[0] ) {
          var address = results[0].address_components;

          jQuery('#billing_postcode').val('');
          jQuery('#billing_address_2').val('');
          jQuery('#billing_city').val('');
          // jQuery('#billing_phone').val('');
          // jQuery('#billing_company').val('');

          for (var i = 0; i < address.length; i++) {
            var addressType = address[i].types[0];


            // filling country field
            if(addressType == 'country'){
              jQuery('#billing_country').val(address[i]['short_name']);
              jQuery('#billing_country').trigger('change');
            }
            // filling street address
            if(addressType == 'street_number' ){
              jQuery('#billing_address_1').val(address[i]['long_name']);
            } else {
              if( typeof ( address[i].types[1] != "undefined" ) ) {
                if( address[i].types[1] == 'sublocality' ) {
                  jQuery('#billing_address_1').val(address[i]['long_name']);
                }
              }
            }
            // adding data to street address
            if(addressType == 'route'){
              var addr = jQuery('#billing_address_1').val();
              if(addr != ''){
                addr = addr +' '+ address[i]['long_name'];
                jQuery('#billing_address_1').val(addr);
              }
            }

            // filling state field
            if(addressType == 'administrative_area_level_1'){
              var state = address[i]['short_name'];
              setTimeout(function(){
                jQuery('#billing_state').val(state);
                jQuery('#billing_state').trigger('change');
              },1500);
            }

            if(addressType == 'neighborhood'){
              jQuery('#billing_address_2').val(address[i]['long_name']);
            } else if(addressType == 'sublocality_level_3'){
              jQuery('#billing_address_2').val(address[i]['long_name']);
            } else if(addressType == 'sublocality_level_2'){
              jQuery('#billing_address_2').val(address[i]['long_name']);
            }

            // filling location
            if(addressType == 'locality'){
              jQuery('#billing_city').val(address[i]['long_name']);
            }
            // filling postal code field
            if(addressType == 'postal_code'){
              jQuery('#billing_postcode').val(address[i]['long_name']);
            }
          }

          if (wcaf.enable_billing_phone) {
            if (results[0].hasOwnProperty("international_phone_number") && results[0].international_phone_number) {
              jQuery('#billing_phone').val(results[0].international_phone_number);
            }
          }

          if (wcaf.enable_billing_company_name) {
            if (results[0].hasOwnProperty("name") && results[0].name) {
              jQuery('#billing_company').val(results[0].name);
            }
          }


        }
        else {
          alert("No results found");// alerting if no results found
        }
      }
      else {
        console.log("Geocoder failed due to: " + status);
      }
  });
}
