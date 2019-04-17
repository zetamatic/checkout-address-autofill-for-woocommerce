/* *
* Author: zetamatic
* @package checkout_address_autofill_for_woocommerce
*/

// for image upload
jQuery(document).ready(function($) {
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
  var autofill,place;
  function initAutocomplete(){
    autofill = new google.maps.places.Autocomplete(document.getElementById('my_field_name'));
    autofill.addListener('place_changed', fillInAddress);
  }

// Filling the address
  function fillInAddress(){
    place = autofill.getPlace();

    jQuery('#billing_postcode').val('');
    jQuery('#billing_address_2').val('');
    jQuery('#billing_city').val('');

    for (var i = 0; i < place.address_components.length; i++) {
      var addressType = place.address_components[i].types[0];

      if(addressType == 'country'){
        jQuery('#billing_country').val(place.address_components[i]['short_name']);
        jQuery('#billing_country').trigger('change');
      }

      if(addressType == 'administrative_area_level_1'){
        jQuery('#billing_state').val(place.address_components[i]['short_name']);
        jQuery('#billing_state').trigger('change');
      }

      if(addressType == 'administrative_area_level_2'){
        jQuery('#billing_address_2').val(place.address_components[i]['long_name']);
      }

      if(addressType == 'locality'){
        jQuery('#billing_city').val(place.address_components[i]['long_name']);
      }

      if(addressType == 'postal_code'){
        jQuery('#billing_postcode').val(place.address_components[i]['long_name']);
      }

    }
  }

// Getting for geolocation support
  function geolocate() {
    if(navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(geoSuccess, geoError);
    } 
    else{
      alert("Geolocation is not supported by this browser.");
    }
  }

// Funtion for error
  function geoError() {
    console.log("Geocoder failed.");
  }

// Function for success and getting coordinates
  function geoSuccess(position) {
    var lat = position.coords.latitude;
    var lng = position.coords.longitude;
    codeLatLng(lat, lng);
  }

// Function to fill address
  var geocoder ;
  function codeLatLng(lat, lng) {
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(lat, lng);
    geocoder.geocode({'latLng': latlng}, function(results, status) {
      if(status == google.maps.GeocoderStatus.OK) {
        if(results[1]) {
          var address = results[0].address_components;
          jQuery('#billing_postcode').val('');
          jQuery('#billing_address_2').val('');
          jQuery('#billing_city').val('');

          for (var i = 0; i < address.length; i++) {
            var addressType = address[i].types[0];

            if(addressType == 'country'){
              jQuery('#billing_country').val(address[i]['short_name']);
              jQuery('#billing_country').trigger('change');
            }

            if(addressType == 'administrative_area_level_1'){
              jQuery('#billing_state').val(address[i]['short_name']);
              jQuery('#billing_state').trigger('change');
            }

            if(addressType == 'administrative_area_level_2'){
              jQuery('#billing_address_2').val(address[i]['long_name']);
            }

            if(addressType == 'locality'){
              jQuery('#billing_city').val(address[i]['long_name']);
            }

            if(addressType == 'postal_code'){
              jQuery('#billing_postcode').val(address[i]['long_name']);
            }
          }

        } 
        else {
          alert("No results found");
        }
      } 
      else {
        console.log("Geocoder failed due to: " + status);
      }
  });
}
