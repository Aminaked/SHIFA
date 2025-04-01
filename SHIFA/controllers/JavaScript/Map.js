function initializeMap() {
    // Get pharmacy details from session storage
    const pharmacyData = JSON.parse(sessionStorage.getItem('medicationDetails'));
    
    if (!pharmacyData) {
        alert("No pharmacy data found. Please search again.");
        window.location.href = '../views/SearchMed.php';
        return;
    }

    // Extract coordinates from session storage data
    const ph_latitude = parseFloat(pharmacyData.ph_latitude);
    const ph_longitude = parseFloat(pharmacyData.ph_longitude);

    // Validate coordinates
    if (isNaN(ph_latitude) || isNaN(ph_longitude)) {
        alert("Invalid or missing latitude/longitude in pharmacy data.");
        return;
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
    pharmacyMarker.bindPopup(`
        <b>${pharmacyData.pharmacy_name || 'Pharmacy'}</b><br>
        ${pharmacyData.address || ''}<br>
        ${pharmacyData.phone_number ? `Phone: ${pharmacyData.phone_number}<br>` : ''}
        Your destination
    `).openPopup();

    // Create travel mode selector
    const modeControl = L.control({position: 'topright'});
    
    modeControl.onAdd = function(map) {
        const div = L.DomUtil.create('div', 'travel-mode-control');
        div.innerHTML = `
            <select id="travelMode" class="form-select">
                <option value="foot">Walking</option>
                <option value="bike">Cycling</option>
                <option value="car">Driving</option>
            </select>
        `;
        return div;
    };
    
    modeControl.addTo(map);

    // Variable to store the routing control
    let routingControl = null;

    // Function to update route based on selected mode
    function updateRoute(position) {
        const userCoords = [position.coords.latitude, position.coords.longitude];
        const selectedMode = document.getElementById('travelMode').value;
        
        // Remove existing route if any
        if (routingControl) {
            map.removeControl(routingControl);
        }

        // Add new route with selected mode
        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(userCoords[0], userCoords[1]),
                L.latLng(pharmacyCoords[0], pharmacyCoords[1])
            ],
            routeWhileDragging: true,
            show: true,
            router: L.Routing.osrmv1({
                serviceUrl: "https://router.project-osrm.org/route/v1",
                profile: selectedMode
            }),
            lineOptions: {
                styles: [
                    {color: selectedMode === 'car' ? 'blue' : 
                           selectedMode === 'bike' ? 'green' : 'orange', 
                     opacity: 0.8, 
                     weight: 5}
                ]
            }
        })
        .on('routingerror', function(e) {
            alert("Error calculating the route: " + e.error.message);
        })
        .addTo(map);

        // Update user marker
        if (!window.userMarker) {
            window.userMarker = L.marker(userCoords).addTo(map);
            window.userMarker.bindPopup("<b>You are here!</b>").openPopup();
        } else {
            window.userMarker.setLatLng(userCoords);
        }
    }

    // Get the user's location and calculate directions
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userCoords = [position.coords.latitude, position.coords.longitude];
                map.setView(userCoords, 13);
                
                // Initial route calculation
                updateRoute(position);
                
                // Update route when travel mode changes
                document.getElementById('travelMode').addEventListener('change', () => {
                    updateRoute(position);
                });
            },
            (error) => {
                alert("Error getting your location: " + error.message);
                map.setView([ph_latitude, ph_longitude], 13);
            }
        );
    } else {
        alert("Geolocation is not supported by this browser.");
        map.setView([ph_latitude, ph_longitude], 13);
    }
}

// Initialize the map when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
});