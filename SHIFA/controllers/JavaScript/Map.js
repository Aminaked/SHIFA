
function initializeMap() {
 
  const urlParams = new URLSearchParams(window.location.search);
  const ph_latitude = parseFloat(urlParams.get('ph_latitude'));
  const ph_longitude = parseFloat(urlParams.get('ph_longitude'));

 
  console.log("Pharmacy Latitude:", ph_latitude);
  console.log("Pharmacy Longitude:", ph_longitude);


  if (isNaN(ph_latitude) || isNaN(ph_longitude)) {
      alert("Invalid or missing latitude/longitude in URL parameters.");
      return; // Exit the function if coordinates are invalid
  }

  // Initialize the map with the pharmacy location
  const map = L.map("map").setView([ph_latitude, ph_longitude], 13);

  // Add OpenStreetMap tiles
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map);

  // Pharmacy coordinates
  const pharmacyCoords = [ph_latitude, ph_longitude];

  // Add a marker for the pharmacy
  const pharmacyMarker = L.marker(pharmacyCoords).addTo(map);
  pharmacyMarker.bindPopup("<b>Pharmacy</b><br>Your destination.").openPopup();

  // Get the user's location and calculate directions
  if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
          (position) => {
              const userCoords = [position.coords.latitude, position.coords.longitude];
              map.setView(userCoords, 13); // Center the map on the user's location

              // Add a marker for the user's location
              const userMarker = L.marker(userCoords).addTo(map);
              userMarker.bindPopup("<b>You are here!</b>").openPopup();

              // Add routing control
              L.Routing.control({
                  waypoints: [
                      L.latLng(userCoords[0], userCoords[1]), // User's location
                      L.latLng(pharmacyCoords[0], pharmacyCoords[1]), // Pharmacy location
                  ],
                  routeWhileDragging: true,
                  router: L.Routing.osrmv1({
                      serviceUrl: "https://router.project-osrm.org/route/v1",
                      profile: "foot", // Options: "foot" (walking), "bike" (cycling), "car" (driving)
                  }),
              })
              .on('routingerror', function (e) {
                  alert("Error calculating the route: " + e.error.message);
              })
              .addTo(map);
          },
          (error) => {
              alert("Error getting your location: " + error.message);
              // Fallback: Center map on pharmacy location
              map.setView([ph_latitude, ph_longitude], 13);
          }
      );
  } else {
      alert("Geolocation is not supported by this browser.");
      // Fallback: Center map on pharmacy location
      map.setView([ph_latitude, ph_longitude], 13);
  }
}
initializeMap();