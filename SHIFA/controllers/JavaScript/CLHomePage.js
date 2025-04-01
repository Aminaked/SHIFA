// Debug session storage at start
console.log('Initial sessionStorage:', JSON.stringify(sessionStorage, null, 2));

if (window.location.search) {
  window.history.replaceState({}, '', window.location.pathname);
  console.log('Cleared existing URL parameters');
}

function toggleMenu(event) {
  event.preventDefault();
  const menu = document.getElementById("menuDropdown");
  menu.style.display = menu.style.display === "block" ? "none" : "block";
}

// Close menu when clicking outside
document.addEventListener("click", function(event) {
  const menu = document.getElementById("menuDropdown");
  const userIcon = document.querySelector(".login");
  
  if (menu && userIcon) {
    if (!userIcon.contains(event.target) && !menu.contains(event.target)) {
      menu.style.display = "none";
    }
  }
});

// Main search functionality
console.log('JavaScript loaded');

document.getElementById('searchForm')?.addEventListener('submit', async function(event) {
  console.log('Form submitted');
  event.preventDefault();

  const medicationInput = document.querySelector('input[name="medication"]');
  if (!medicationInput) {
    console.error('Medication input not found');
    return;
  }

  const medication = medicationInput.value.trim();
  if (!medication) {
    alert('Please enter a medication name');
    return;
  }

  try {
    console.log('Requesting geolocation...');
    const position = await new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      });
    });

    console.log('Geolocation success:', position.coords);
    await searchPharmacies(medication, position.coords);
  } catch (error) {
    console.error('Error:', error);
    if (error.code === error.PERMISSION_DENIED) {
      alert('Location access was denied. Please enable location services to use this feature.');
    } else {
      alert('Error getting location: ' + error.message);
    }
  }
});

async function searchPharmacies(medication, coords) {
  const resultsDiv = document.getElementById('results');
  if (!resultsDiv) {
    console.error('Results div not found');
    return;
  }

  resultsDiv.innerHTML = '<p class="loading">Searching pharmacies...</p>';

  try {
    console.log('About to send fetch request');
    const response = await fetch(`http://localhost/SHIFA/SHIFA/controllers/SearchMed.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        medication: medication,
        user_lat: coords.latitude,
        user_lon: coords.longitude
      })
    });

    console.log('Received response, status:', response.status);
    
    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`Server error: ${response.status} - ${errorText}`);
    }

    const results = await response.json();
    console.log('Received results:', results);
    displayResults(results);
  } catch (error) {
    console.error('Fetch error:', error);
    resultsDiv.innerHTML = `
      <p class="error">Error fetching results: ${error.message}</p>
      <button onclick="window.location.reload()">Try Again</button>
    `;
  }
}

function displayResults(results) {
  const resultsDiv = document.getElementById('results');
  resultsDiv.innerHTML = '';

  // Enhanced error checking
  if (!results) {
    console.error('No results received from server');
    showError(resultsDiv, 'No data received from server. Please try again.');
    return;
  }

  if (results.error) {
    console.error('Server returned error:', results.error);
    showError(resultsDiv, results.error);
    return;
  }

  if (!results.success) {
    console.error('Unsuccessful request:', results.message || 'No success flag in response');
    showError(resultsDiv, results.message || 'Request was not successful');
    return;
  }

  if (!Array.isArray(results.data)) {
    console.error('Invalid data format:', results);
    showError(resultsDiv, 'Invalid data format received from server');
    return;
  }

  if (results.data.length === 0) {
    resultsDiv.innerHTML = `
      <div class="no-results">
        <p>No pharmacies found with the desired medication.</p>
        <button onclick="document.querySelector('input[name=\"medication\"]').focus()">
          Try a different search
        </button>
      </div>
    `;
    return;
  }

  // Process and display valid results
  results.data.forEach(pharmacy => {
    // Validate required pharmacy fields
    if (!pharmacy.pharmacy_id || !pharmacy.pharmacy_name || !pharmacy.Brand_Name) {
      console.warn('Incomplete pharmacy data:', pharmacy);
      return; // Skip this incomplete entry
    }

    const pharmacyDiv = document.createElement('div');
    pharmacyDiv.className = 'pharmacy';
    pharmacyDiv.innerHTML = `
      <h3>${pharmacy.pharmacy_name}</h3>
      <p><strong>Address:</strong> ${pharmacy.address || 'Not available'}</p>
      <p><strong>Stock:</strong> ${pharmacy.stock || 'Unknown'}</p>
      <p><strong>Brand Name:</strong> ${pharmacy.Brand_Name}</p>
      <p><strong>Generic Name:</strong> ${pharmacy.Generic_Name || 'Not specified'}</p>
      ${pharmacy.distance ? `<p><strong>Distance:</strong> ${pharmacy.distance} miles</p>` : ''}
    `;

    pharmacyDiv.addEventListener('click', () => {
      // Validate coordinates before storing
      const ph_latitude = parseFloat(pharmacy.ph_latitude);
      const ph_longitude = parseFloat(pharmacy.ph_longitude);

      // Debug before removing
      console.log('Before removing medicationDetails from sessionStorage:', 
        sessionStorage.getItem('medicationDetails'));
      
      sessionStorage.removeItem('medicationDetails');
      
      // Debug after removing
      console.log('After removing medicationDetails from sessionStorage:', 
        sessionStorage.getItem('medicationDetails'));

      const medDetails = {
        pharmacy_id: pharmacy.pharmacy_id,
        pharmacy_name: pharmacy.pharmacy_name,
        address: pharmacy.address,
        distance: pharmacy.distance,
        brand_name: pharmacy.Brand_Name,
        generic_name: pharmacy.Generic_Name,
        stock: pharmacy.stock,
        email: pharmacy.email,
        phone_number: pharmacy.phone_number,
        ph_longitude: ph_longitude,
        ph_latitude: ph_latitude
      };
      
      // Debug before setting
      console.log('About to store in sessionStorage:', medDetails);
      
      sessionStorage.setItem('medicationDetails', JSON.stringify(medDetails));
      
      // Debug after setting
      console.log('Stored in sessionStorage:', 
        sessionStorage.getItem('medicationDetails'));
      console.log('Full sessionStorage:', JSON.stringify(sessionStorage, null, 2));
      
      window.location.href = `../views/MedDetails.php`;
    });

    resultsDiv.appendChild(pharmacyDiv);
  });
}

function showError(container, message) {
  container.innerHTML = `
    <div class="error">
      <p>${message}</p>
      <div class="error-actions">
        <button onclick="window.location.reload()">Refresh Page</button>
        <button onclick="document.getElementById('searchForm').reset(); document.querySelector('input[name=\"medication\"]').focus()">
          Try New Search
        </button>
      </div>
    </div>
  `;
}

// Add event listener to log session storage changes
window.addEventListener('storage', function(event) {
  console.log('Storage event detected:', event);
  console.log('Current sessionStorage:', JSON.stringify(sessionStorage, null, 2));
});