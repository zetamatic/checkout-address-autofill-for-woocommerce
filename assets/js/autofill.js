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
        autofill_for_shipping = new google.maps.places.Autocomplete(document.getElementById('shipping_autofill_checkout_field'));

        if( wcaf.selectedCountry.length > 0  && wcaf.selectedCountry !== undefined ) {
          autofill_for_shipping.setComponentRestrictions(
                {'country': wcaf.selectedCountry });
        }
        autofill_for_shipping.addListener('place_changed', fillInShippingAddress);
      }

    }

    autofill = new google.maps.places.Autocomplete(document.getElementById('autofill_checkout_field'));

    if( wcaf.selectedCountry.length > 0  && wcaf.selectedCountry !== undefined ) {
      autofill.setComponentRestrictions(
            {'country': wcaf.selectedCountry });
    }

    autofill.addListener('place_changed', fillInBillingAddress);
  }


// Filling the Shipping address
  function fillInShippingAddress() {

    if( !wcaf.autofill_for_shipping ) {
      return;
    }

    place = autofill_for_shipping.getPlace();

    jQuery('#shipping_postcode').val('');
    jQuery('#shipping_address_1').val('');
    jQuery('#shipping_address_2').val('');
    jQuery('#shipping_city').val('');
    // jQuery('#shipping_company').val('');

    for (var i = 0; i < place.address_components.length; i++) {
      var addressType = place.address_components[i].types[0];
      // filling country field
      if(addressType == 'country'){
        jQuery('#shipping_country').val(place.address_components[i]['short_name']);
        jQuery('#shipping_country').trigger('change');
      }
      // filling street address field
      if(addressType == 'street_number'){
          jQuery('#shipping_address_1').val(place.address_components[i]['long_name']);
      } else {
        if( typeof ( place.address_components[i].types[1] != "undefined" ) ) {
          if( place.address_components[i].types[1] == 'sublocality' ) {
            jQuery('#shipping_address_1').val(place.address_components[i]['long_name']);
          }
        }
      }
      // adding data to street address field
      if(addressType == 'route') {
        var addr = jQuery('#shipping_address_1').val();
        if(addr != ''){
          addr = addr +' '+ place.address_components[i]['long_name'];
          jQuery('#shipping_address_1').val(addr);
        } else {
          jQuery('#shipping_address_1').val(place.address_components[i]['long_name']);
        }
      }

      // filling state field
      if(addressType == 'administrative_area_level_1'){
        var state = place.address_components[i]['short_name'];
        setTimeout(function(){
          jQuery('#shipping_state').val(state);
          jQuery('#shipping_state').trigger('change');
        },1500);
      }

      if(addressType == 'neighborhood'){
        jQuery('#shipping_address_2').val(place.address_components[i]['long_name']);
      } else if(addressType == 'sublocality_level_3'){
        jQuery('#shipping_address_2').val(place.address_components[i]['long_name']);
      } else if(addressType == 'sublocality_level_2'){
        jQuery('#shipping_address_2').val(place.address_components[i]['long_name']);
      }

      // filling location
      if(addressType == 'locality'){
        jQuery('#shipping_city').val(place.address_components[i]['long_name']);
      }
      // filling postal code
      if(addressType == 'postal_code'){
        jQuery('#shipping_postcode').val(place.address_components[i]['long_name']);
      }

    }

    if( wcaf.enable_shipping_company_name ) {
      if(place.hasOwnProperty("name") && place.name) {
        jQuery('#shipping_company').val(place.name);
      }
    }

  }


// Filling the address
  function fillInBillingAddress(){
    place = autofill.getPlace();
    jQuery('#billing_postcode').val('');
    jQuery('#billing_address_2').val('');
    jQuery('#billing_address_1').val('');
    jQuery('#billing_city').val('');
    // jQuery('#billing_phone').val('');
    // jQuery('#billing_company').val('');

    for (var i = 0; i < place.address_components.length; i++) {
      var addressType = place.address_components[i].types[0];
      // filling country field
      if(addressType == 'country'){
        jQuery('#billing_country').val(place.address_components[i]['short_name']);
        jQuery('#billing_country').trigger('change');
      }
      // filling street address field
      if(addressType == 'street_number'){
          jQuery('#billing_address_1').val(place.address_components[i]['long_name']);
      } else {
        if( typeof ( place.address_components[i].types[1] != "undefined" ) ) {
          if( place.address_components[i].types[1] == 'sublocality' ) {
            jQuery('#billing_address_1').val(place.address_components[i]['long_name']);
          }
        }
      }
      // adding data to street address field
      if(addressType == 'route') {
        var addr = jQuery('#billing_address_1').val();
        if(addr != ''){
          addr = addr +' '+ place.address_components[i]['long_name'];
          jQuery('#billing_address_1').val(addr);
        } else {
          jQuery('#billing_address_1').val(place.address_components[i]['long_name']);
        }
      }

      // filling state field
      if(addressType == 'administrative_area_level_1'){
        var state = place.address_components[i]['short_name'];
        setTimeout(function(){
          jQuery('#billing_state').val(state);
          jQuery('#billing_state').trigger('change');
        },1500);
      }

      // filling second address field
      if(addressType == 'neighborhood'){
        jQuery('#billing_address_2').val(place.address_components[i]['long_name']);
      } else if(addressType == 'sublocality_level_3'){
        jQuery('#billing_address_2').val(place.address_components[i]['long_name']);
      } else if(addressType == 'sublocality_level_2'){
        jQuery('#billing_address_2').val(place.address_components[i]['long_name']);
      }

      // filling location
      if(addressType == 'locality'){
        jQuery('#billing_city').val(place.address_components[i]['long_name']);
      }
      // filling postal code
      if(addressType == 'postal_code'){
        jQuery('#billing_postcode').val(place.address_components[i]['long_name']);
      }

    }

    if( wcaf.enable_billing_phone ) {
      if (place.hasOwnProperty("international_phone_number") && place.international_phone_number){
        jQuery('#billing_phone').val(place.international_phone_number);
      }
    }

    if( wcaf.enable_billing_company_name ) {
      if(place.hasOwnProperty("name") && place.name){
        jQuery('#billing_company').val(place.name);
      }
    }

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
