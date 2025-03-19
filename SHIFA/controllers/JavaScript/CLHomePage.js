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



function displayResults(results) {
  const resultsDiv = document.getElementById('results');
  resultsDiv.innerHTML = ''; 

  if (results.length > 0) {
    results.forEach(pharmacy => {
      const pharmacyDiv = document.createElement('div');
      pharmacyDiv.className = 'pharmacy';
      pharmacyDiv.innerHTML = `
        <h3>${pharmacy.pharmacy_name}</h3>
        <p><strong>Address:</strong> ${pharmacy.address}</p>
        <p><strong>Distance:</strong> ${pharmacy.distance}</p>
        <p><strong>Stock:</strong> ${pharmacy.stock}</p>
        <p><strong>Brand Name:</strong> ${pharmacy.Brand_Name}</p>
        <p><strong>Generic Name:</strong> ${pharmacy.Generic_Name}</p>
      `;

     
      pharmacyDiv.addEventListener('click', () => {
     
        window.location.href = `../views/MedDetails.php?pharmacy_id=${pharmacy.pharmacy_id}&brand_name=${encodeURIComponent(pharmacy.Brand_Name)}&generic_name=${encodeURIComponent(pharmacy.Generic_Name)}&stock=${pharmacy.stock}&distance=${pharmacy.distance}&pharmacy_name=${encodeURIComponent(pharmacy.pharmacy_name)}&address=${encodeURIComponent(pharmacy.address)}&longitude=${encodeURIComponent(pharmacy.longitude)}&latitude=${encodeURIComponent(pharmacy.latitude)}`;
      });

      resultsDiv.appendChild(pharmacyDiv);
    });
  } else {
    resultsDiv.innerHTML = '<p>No pharmacies found with the desired medication.</p>';
  }
}

document.getElementById('searchForm').addEventListener('submit', async function (event) {
  event.preventDefault(); 

 
  const medication = document.querySelector('input[name="medication"]').value;

  // Get user's location
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(async function (position) {
      const userLat = position.coords.latitude;
      const userLon = position.coords.longitude;

      // Fetch results from the backend
      try {
        const response = await fetch(`http://localhost/SHIFA/SHIFA/controllers/SearchMed.php`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `medication=${encodeURIComponent(medication)}&user_lat=${userLat}&user_lon=${userLon}`,
        });

        if (!response.ok) {
          throw new Error('Network response was not ok');
        }

        const results = await response.json();
        displayResults(results); // Display the results
      } catch (error) {
        console.error('Error fetching results:', error);
        document.getElementById('results').innerHTML = '<p>Error fetching results. Please try again.</p>';
      }
    }, function (error) {
      alert('Error: ' + error.message);
    });
  } else {
    alert('Geolocation is not supported by this browser.');
  }
});