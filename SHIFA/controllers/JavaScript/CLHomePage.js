
console.log('Initial sessionStorage:', JSON.stringify(sessionStorage, null, 2));

sessionStorage.removeItem('searchParams');
console.log('Cleared previous searchParams from sessionStorage');

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

// Main search functionality
console.log('Search page JavaScript loaded');

document.getElementById('searchForm')?.addEventListener('submit', async function (event) {
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
    
    // Store search parameters in sessionStorage
    sessionStorage.setItem('searchParams', JSON.stringify({
      medication: medication,
      coords: {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude
      }
    }));
    
    // Redirect to results page
    window.location.href = '../views/MedSearchResults.php';
  } catch (error) {
    console.error('Error:', error);
    if (error.code === error.PERMISSION_DENIED) {
      alert('Location access was denied. Please enable location services to use this feature.');
    } else {
      alert('Error getting location: ' + error.message);
    }
  }
});

// Storage event listener
window.addEventListener('storage', function (event) {
  console.log('Storage event detected:', event);
  console.log('Current sessionStorage:', JSON.stringify(sessionStorage, null, 2));
});