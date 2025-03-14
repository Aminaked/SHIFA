function toggleMenu(event) {
  event.preventDefault(); 
  var menu = document.getElementById("menuDropdown");
  menu.style.display = menu.style.display === "block" ? "none" : "block";
}

document.addEventListener("click", function (event) {
  var menu = document.getElementById("menuDropdown");
  var userIcon = document.querySelector(".login");
  if (!userIcon.contains(event.target) && !menu.contains(event.target)) {
    menu.style.display = "none";
  }
});

if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(function(position) {
   
    var userLat = position.coords.latitude;
    var userLon = position.coords.longitude;

    
    var latInput = document.createElement('input');
    latInput.type = 'hidden';
    latInput.name = 'user_lat';
    latInput.value = userLat;
    document.querySelector('form').appendChild(latInput);

    var lonInput = document.createElement('input');
    lonInput.type = 'hidden';
    lonInput.name = 'user_lon';
    lonInput.value = userLon;
    document.querySelector('form').appendChild(lonInput);
  }, function(error) {
    alert('Error: ' + error.message);
  });
} else {
  alert('Geolocation is not supported by this browser.');
}

// Function to display results
// Function to display results
function displayResults(results) {
  const resultsDiv = document.getElementById('results');
  resultsDiv.innerHTML = ''; // Clear previous results

  if (results.length > 0) {
      results.forEach(pharmacy => {
          const pharmacyDiv = document.createElement('div');
          pharmacyDiv.className = 'pharmacy';
          pharmacyDiv.innerHTML = `
              <h3>${pharmacy.pharmacy_name}</h3>
              <p><strong>Address:</strong> ${pharmacy.address}</p>
              <p><strong>Distance:</strong> ${pharmacy.distance}</p>
              <p><strong>Stock:</strong> ${pharmacy.stock}</p>
              <p><strong>Brand Name:</strong> ${pharmacy.brand_name}</p>
              <p><strong>Generic Name:</strong> ${pharmacy.generic_name}</p>
          `;
          resultsDiv.appendChild(pharmacyDiv);
      });
  } else {
      resultsDiv.innerHTML = '<p>No pharmacies found with the desired medication.</p>';
  }
}

// Fetch and display results when the page loads
window.onload = async function () {
  // Extract query parameters from the URL
  const urlParams = new URLSearchParams(window.location.search);
  const medication = urlParams.get('medication');
  const userLat = urlParams.get('user_lat');
  const userLon = urlParams.get('user_lon');

  if (medication && userLat && userLon) {
      // Fetch results from the backend
      const response = await fetch(`http://localhost/SHIFA/SHIFA/controllers/SearchMed.php?medication=${medication}&user_lat=${userLat}&user_lon=${userLon}`);
      const results = await response.json();

      // Display the results on the homepage
      displayResults(results);
  }
};