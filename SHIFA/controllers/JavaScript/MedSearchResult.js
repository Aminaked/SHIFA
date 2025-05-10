

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
    const response = await fetch(`http://localhost/SHIFA/SHIFA/controllers/SearchMed.php`, {
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
function extractBrandName(productName) {
  if (!productName) return '';

  // Enhanced patterns for global medication formats (English/French)
  const patternsToRemove = [
    // Dosage/strength patterns (with international units)
    /\s*\d+[\.,]?\d*\s*(?:mg|μg|mcg|g|ml|ui|iu|meq|%|mg\/ml|ml\/g|uf|µg|mcg)\b/gi,  // Dosage
    /\s*\d+\s*(?:mg|ml)\/\s*\d+\s*(?:mg|ml)/gi,  // Ratios (e.g., "25mg/5ml")
    
    // Packaging/form indicators (English + French)
    /\s*\b(?:b\/|boite de |bo[îi]te de |plaquette de )\d+/gi,  // Packaging
    /\s*\b(?:tab|cap|comp|comprim[ée]|gelule|gel|supp|pellets|susp|inj|crème|pch|cp|sachet|lyoph)\b/gi,  // Forms
    
    // Special characters cleanup
    /\s*[-–+]\s*(?:lib|ec|sr|lp|sa|cf|ih|iv|xj|xn)\b/gi,  // Extended release markers
    /\s*\([^)]*\)/g,  // Parenthetical content
    /\s*\b(?:anti\s*|sans\s*|avec\s*|plus\s*|extra\s*)/gi  // Descriptive prefixes
  ];

  // Multi-step cleaning process
  return productName
    .replace(/[®™]/g, '')  // Remove trademark symbols first
    .split(/[\s\/]/)[0]     // Keep only first part before space/slash
    .replace(new RegExp(`(${patternsToRemove.map(p => p.source).join('|')})`, 'g'), '')
    .replace(/\s+/g, ' ')   // Collapse whitespace
    .trim()
    .replace(/(?:^|\s)\w/g, m => m.toUpperCase());  // Title case
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
    const profilePhoto = pharmacy.profile_photo 
    ? `../uploads/${pharmacy.profile_photo} `
    : '../public/images/client.jpg';

    // Extract brand name cleaned
    const brandName = extractBrandName(pharmacy.Produit);

    pharmacyDiv.innerHTML = `
        <div class="pharmacy-header">
        <img src="${profilePhoto}" alt="${pharmacy.pharmacy_name} Pharmacy" class="pharmacy-logo">
      <h3>${pharmacy.pharmacy_name}</h3></div>
      <p><strong>Address:</strong> ${pharmacy.address || 'Not available'}</p>
      <p><strong>Stock:</strong> ${pharmacy.stock || 'Unknown'}</p>
      <p><strong>Price:</strong> ${pharmacy.price || 'Unknown'}</p>
      <p><strong>Brand Name:</strong> ${pharmacy.Produit}</p>
      <p><strong>Generic Name:</strong> <span id="generic-name-${pharmacy.pharmacy_id}">Loading...</span></p>
      ${pharmacy.distance ? `<p><strong>Distance:</strong> ${pharmacy.distance} miles</p>` : ''
         }
         <button class="details-btn">View Details</button>
    `;

    // Fetch and update generic name asynchronously
    const genericNameElement = pharmacyDiv.querySelector(`#generic-name-${pharmacy.pharmacy_id}`);
    if (brandName) {
      // Use cleaned brandName for API queries
      fetchGenericNameFDA(brandName, genericNameElement);
    } else {
      genericNameElement.textContent = 'Not available';
    }

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
          brand_name: pharmacy.Produit,
          price:pharmacy.price,
          stock: pharmacy.stock,
          email: pharmacy.email,
          phone_number: pharmacy.phone_number,
          ph_longitude: ph_longitude,
          ph_latitude: ph_latitude,
          profile_photo: pharmacy['profile_photo'] ?? null 

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

// Async functions to fetch generic name from FDA and RxNorm APIs

async function fetchGenericNameRxNorm(brandName, genericNameElement) {
  try {
    
    console.log(`Extracted brand name for RxNorm API: ${brandName}`);
    console.log(`Fetching generic name from RxNorm for brand: ${brandName}`);
    // Step 1: Fetch RXCUI for the brand name
    const rxcuiRes = await fetch(
      `https://rxnav.nlm.nih.gov/REST/rxcui.json?name=${encodeURIComponent(brandName)}&search=2`
    );

    if (!rxcuiRes.ok) throw new Error(`RxNorm RXCUI fetch error: ${rxcuiRes.status}`);

    const rxcuiData = await rxcuiRes.json();
    const rxcui = rxcuiData.idGroup?.rxnormId?.[0];

    if (!rxcui) {
      genericNameElement.textContent = 'Not available';
      return;
    }

    // Step 2: Fetch generic name using related.json?tty=IN endpoint only
    let genericName = null;
    try {
      const relatedRes = await fetch(
        `https://rxnav.nlm.nih.gov/REST/rxcui/${rxcui}/related.json?tty=IN`
      );
      if (relatedRes.ok) {
        const relatedData = await relatedRes.json();
        genericName = relatedData.relatedGroup?.conceptGroup?.[0]?.conceptProperties?.[0]?.name;
      }
    } catch (err) {
      console.warn('Failed to fetch related concepts for generic name...', err);
    }

    genericNameElement.textContent = genericName || 'Not available';

  } catch (error) {
    console.error('RxNorm API Error:', error);
    genericNameElement.textContent = 'Not available';
  }
}

async function fetchGenericNameFDA(brandName, genericNameElement) {
  try {
    brandName = extractBrandName(brandName);
    console.log(`Extracted brand name for FDA API: ${brandName}`);
    console.log(`Fetching generic name from FDA for brand: ${brandName}`);
    // Fetch generic name from FDA API by brand name
    const response = await fetch(
      `https://api.fda.gov/drug/label.json?search=openfda.brand_name:"${encodeURIComponent(brandName)}"&limit=1`
    );

    if (!response.ok) throw new Error(`FDA API fetch error: ${response.status}`);

    const data = await response.json();
    const results = data.results?.[0];
    const genericName = results?.openfda?.generic_name?.[0] || null;

    if (genericName) {
      genericNameElement.textContent = genericName;
    } else {
      // Fallback to RxNorm API if FDA API has no generic name
      await fetchGenericNameRxNorm(brandName, genericNameElement);
    }
  } catch (error) {
    console.error('FDA API Error:', error);
    // Fallback to RxNorm API on error
    await fetchGenericNameRxNorm(brandName, genericNameElement);
  }
}

function fetchFDAData(medData, brandName) {
  // Placeholder for any additional FDA data fetching after generic name is set
  // This can be implemented as needed
}

// Storage event listener
window.addEventListener('storage', function (event) {
  console.log('Storage event detected:', event);
  console.log('Current sessionStorage:', JSON.stringify(sessionStorage, null, 2));
});
