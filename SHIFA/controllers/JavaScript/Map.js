// Debugging wrapper
function debugLog(message, data = null) {
    console.log(`[DEBUG][${new Date().toISOString()}] ${message}`);
    if (data) console.log(data);
}

function initializeMap() {
    debugLog("Map initialization started");
    
    try {
        // Debug session storage
        const rawData = sessionStorage.getItem('medicationDetails');
        debugLog("Session storage raw data:", rawData);

        if (!rawData) {
            debugLog("No medicationDetails found in sessionStorage");
            alert("No pharmacy data found. Please search again.");
            window.location.href = '../views/SearchMed.php';
            return;
        }

        const pharmacyData = JSON.parse(rawData);
        debugLog("Parsed pharmacy data:", pharmacyData);

        // Validate coordinates
        const ph_latitude = parseFloat(pharmacyData.ph_latitude);
        const ph_longitude = parseFloat(pharmacyData.ph_longitude);
        debugLog("Coordinates parsed:", { ph_latitude, ph_longitude });

        if (isNaN(ph_latitude)) {
            debugLog("Invalid latitude:", pharmacyData.ph_latitude);
            throw new Error("Invalid latitude in pharmacy data");
        }
        if (isNaN(ph_longitude)) {
            debugLog("Invalid longitude:", pharmacyData.ph_longitude);
            throw new Error("Invalid longitude in pharmacy data");
        }

        // Initialize map
        debugLog("Initializing Leaflet map");
        const map = L.map("map").setView([ph_latitude, ph_longitude], 13);
        
        // Tile layer
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);

        // Pharmacy marker
        const pharmacyCoords = [ph_latitude, ph_longitude];
        const pharmacyMarker = L.marker(pharmacyCoords).addTo(map);
        pharmacyMarker.bindPopup(`
            <b>${pharmacyData.pharmacy_name || 'Pharmacy'}</b><br>
            ${pharmacyData.address || ''}<br>
            ${pharmacyData.phone_number ? `Phone: ${pharmacyData.phone_number}<br>` : ''}
            <button id="get-directions" class="map-btn">Get Directions</button>
        `).openPopup();

        // Add click handler to the button in the popup
        pharmacyMarker.on('popupopen', function() {
            debugLog("Popup opened, attaching event listeners");
            const dirBtn = document.getElementById('get-directions');
            if (dirBtn) {
                dirBtn.addEventListener('click', function(e) {
                    debugLog("Get Directions button clicked");
                    e.stopPropagation();
                    handleGetDirections(pharmacyCoords);
                });
            } else {
                debugLog("Get Directions button not found in popup");
            }
        });

        // Handle directions request
        function handleGetDirections(destinationCoords) {
            debugLog("Handling directions request to:", destinationCoords);
            
            if (!navigator.geolocation) {
                debugLog("Geolocation not supported");
                alert("Geolocation is not supported by your browser.");
                return;
            }

            debugLog("Requesting user location...");
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userCoords = [
                        position.coords.latitude, 
                        position.coords.longitude
                    ];
                    debugLog("User location obtained:", userCoords);
                    
                    // Open in Google Maps
                    const mapsUrl = `https://www.google.com/maps/dir/?api=1&origin=${
                        userCoords.join(',')
                    }&destination=${
                        destinationCoords.join(',')
                    }&travelmode=driving`;
                    
                    debugLog("Opening Google Maps URL:", mapsUrl);
                    window.open(mapsUrl, '_blank');
                },
                (error) => {
                    debugLog("Geolocation error:", {
                        code: error.code,
                        message: error.message
                    });
                    
                    // Fallback to just showing destination
                    const mapsUrl = `https://www.google.com/maps/?q=${
                        destinationCoords.join(',')
                    }`;
                    window.open(mapsUrl, '_blank');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                }
            );
        }

        // Rest of your existing routing code...
        // ... [keep your existing routing control code]

    } catch (error) {
        debugLog("Map initialization failed:", error);
        alert(`Map error: ${error.message}`);
    }
}

// Initialize with enhanced debugging
document.addEventListener('DOMContentLoaded', function() {
    debugLog("DOM fully loaded, initializing map");
    initializeMap();
});