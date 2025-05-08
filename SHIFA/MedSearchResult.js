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
document.addEventListener("click", function (event) {
  const menu = document.getElementById("menuDropdown");
  const userIcon = document.querySelector(".login");

  if (menu && userIcon) {
    if (!userIcon.contains(event.target) && !menu.contains(event.target)) {
      menu.style.display = "none";
    }
  }
});

// Results page functionality
console.log('Results page JavaScript loaded');

document.addEventListener('DOMContentLoaded', async function() {
  const resultsDiv = document.getElementById('results');
  const storedParams = sessionStorage.getItem('searchParams');

  if (!storedParams) {
    showError('No search parameters found. Please start a new search.');
    return;
  }

  try {
    const { medication, coords } = JSON.parse(storedParams);
    await searchPharmacies(medication, coords);
  } catch (error) {
    console.error('Error parsing search parameters:', error);
    showError('Invalid search parameters. Please start a new search.');
  }
});

async function searchPharmacies(medication, coords) {
  const resultsDiv = document.getElementById('results');
  resultsDiv.innerHTML = '<p class="loading">Searching pharmacies...</p>';

  try {
    const response = await fetch(`http://localhost/SHIFA-main/SHIFA/controllers/SearchMed.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        medication: medication,
        user_lat: coords.latitude,
        user_lon: coords.longitude
      })
    });

    if (!response.ok) throw new Error(`Server error: ${response.status}`);
    const results = await response.json();
    
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

  if (!results || results.error || !results.success || !Array.isArray(results.data)) {
    showError(results?.error || 'Invalid response from server');
    return;
  }

  if (results.data.length === 0) {
    resultsDiv.innerHTML = `
      <div class="no-results">
        <p>No pharmacies found with the desired medication.</p>
        <button onclick="location.href='index.html'">Try a different search</button>
      </div>
    `;
    return;
  }

  results.data.forEach(pharmacy => {
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

function showError(message) {
  const resultsDiv = document.getElementById('results');
  resultsDiv.innerHTML = `
    <div class="error">
      <p>${message}</p>
      <div class="error-actions">
        <button onclick="location.href='index.html'">New Search</button>
        <button onclick="window.location.reload()">Refresh Page</button>
      </div>
    </div>
  `;
}

// Storage event listener
window.addEventListener('storage', function (event) {
  console.log('Storage event detected:', event);
  console.log('Current sessionStorage:', JSON.stringify(sessionStorage, null, 2));
});