const urlParams = new URLSearchParams(window.location.search);
const latitude = parseFloat(urlParams.get('latitude'));
const longitude = parseFloat(urlParams.get('longitude'));


function initMap() {
  const pharmacyLocation = { lat: latitude, lng: longitude };

  const map = new google.maps.Map(document.getElementById('map'), {
    zoom: 15,
    center: pharmacyLocation,
  });
  new google.maps.Marker({
    map: map,
    position: pharmacyLocation,
    title: 'Pharmacy Location',
  });
}