const urlParams = new URLSearchParams(window.location.search);
const latitude = parseFloat(urlParams.get('latitude'));
const longitude = parseFloat(urlParams.get('longitude'));
const map = L.map("map").setView([37.7749, -122.4194], 13); // San Francisco coordinates and zoom level

// Add OpenStreetMap tiles
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution:
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
}).addTo(map);

// Add a marker
const marker = L.marker([37.7749, -122.4194]).addTo(map);
marker.bindPopup("<b>Hello!</b><br>This is San Francisco.").openPopup();