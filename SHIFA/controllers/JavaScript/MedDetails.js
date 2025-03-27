
const elements = {
  brandName: document.getElementById('brand-name'),
  pharmacyName: document.getElementById('pharmacy-name'),
  address: document.getElementById('address'),
  distance: document.getElementById('distance'),
  stock: document.getElementById('stock'),
  genericName: document.getElementById('generic-name'),
  indications: document.getElementById('indications'),
  dosage: document.getElementById('dosage'),
  warnings: document.getElementById('warnings'),
  contraindications: document.getElementById('contraindications'),
  loadingSpinner: document.getElementById('loading-spinner'),
  locateButton: document.getElementById('locate-button')
};

const urlParams = new URLSearchParams(window.location.search);
const params = {
  pharmacyId: urlParams.get('pharmacy_id'),
  brandName: decodeURIComponent(urlParams.get('brand_name')),
  genericName: decodeURIComponent(urlParams.get('generic_name')),
  stock: urlParams.get('stock'),
  distance: urlParams.get('distance'),
  pharmacyName: decodeURIComponent(urlParams.get('pharmacy_name')),
  address: decodeURIComponent(urlParams.get('address')),
  latitude: urlParams.get('latitude'),
  longitude: urlParams.get('longitude')
};


const displayBasicInfo = () => {
  elements.brandName.textContent = params.brandName;
  elements.pharmacyName.textContent = params.pharmacyName;
  elements.address.textContent = params.address;
  elements.distance.textContent = `${params.distance} miles`;
  elements.stock.textContent = params.stock === '1' ? 'In Stock' : 'Out of Stock';
  elements.genericName.textContent = params.genericName;
};


const fetchFDAData = async () => {
  const searchQuery = params.brandName || params.genericName;
  if (!searchQuery) return;


  const cacheKey = `fda_${searchQuery.toLowerCase().replace(/\s+/g, '_')}`;

 
  const cachedData = localStorage.getItem(cacheKey);
  if (cachedData) {
    const { data, timestamp } = JSON.parse(cachedData);
    const isCacheValid = (Date.now() - timestamp) < 3600000; // 1 hour in ms

    if (isCacheValid) {
      displayFDAData(data);
      return;
    }
  }

  
  try {
    elements.loadingSpinner?.classList.remove('hidden');
    
   
    let apiUrl = `https://api.fda.gov/drug/label.json?search=openfda.brand_name:"${params.brandName}"&limit=1`;
    let response = await fetch(apiUrl);
    let data = await response.json();

  
    if (!data.results?.length && params.genericName) {
      apiUrl = `https://api.fda.gov/drug/label.json?search=openfda.generic_name:"${params.genericName}"&limit=1`;
      response = await fetch(apiUrl);
      data = await response.json();
    }

    if (data.results?.length) {
      const medicationData = data.results[0];
      
      // Cache with timestamp (valid for 1 hour)
      localStorage.setItem(
        cacheKey,
        JSON.stringify({
          data: medicationData,
          timestamp: Date.now()
        })
      );
      
      displayFDAData(medicationData);
    } else {
      displayFDAData(null); // No data found
    }
  } catch (error) {
    console.error("FDA API Error:", error);
    displayFDAData(null, true); // Show error state
  } finally {
    elements.loadingSpinner?.classList.add('hidden');
  }
};

// ====== 5. DISPLAY FDA DATA (WITH FALLBACKS) ====== //
const displayFDAData = (data, isError = false) => {
  const defaultText = {
    indications: "No usage information available",
    dosage: "Dosage information not provided",
    warnings: "No warnings listed",
    contraindications: "No contraindications listed"
  };

  if (isError) {
    elements.indications.textContent = "⚠️ Failed to load medication details";
    return;
  }

  if (!data) {
    Object.keys(defaultText).forEach(key => {
      elements[key].textContent = defaultText[key];
    });
    return;
  }

  // Clean FDA text (removes [1], [2], etc.)
  const cleanText = (text) => text?.[0]?.replace(/^\s*\[\d+\]\s*/g, '') || '';

  elements.indications.textContent = cleanText(data.indications_and_usage) || defaultText.indications;
  elements.dosage.textContent = cleanText(data.dosage_and_administration) || defaultText.dosage;
  elements.warnings.textContent = cleanText(data.warnings) || defaultText.warnings;
  elements.contraindications.textContent = cleanText(data.contraindications) || defaultText.contraindications;
};


document.addEventListener('DOMContentLoaded', () => {
  displayBasicInfo();
  fetchFDAData();
  
  elements.locateButton?.addEventListener('click', () => {
    window.location.href = `../views/Map.php?ph_latitude=${params.latitude}&ph_longitude=${params.longitude}`;
  });
});