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

// Get medication details from sessionStorage
const medicationDetails = JSON.parse(sessionStorage.getItem('medicationDetails'));
if (!medicationDetails) {
  console.error('No medication details found');
  window.location.href = '../views/SearchMed.php';
 
}

// Display basic medication info
elements.brandName.textContent = medicationDetails.brand_name;
elements.pharmacyName.textContent = medicationDetails.pharmacy_name;
elements.address.textContent = medicationDetails.address;
elements.distance.textContent = medicationDetails.distance;
elements.stock.textContent = medicationDetails.stock;
elements.genericName.textContent = medicationDetails.generic_name;

const fetchFDAData = async () => {
  // Use medicationDetails instead of undefined 'params'
  const searchQuery = medicationDetails.brand_name || medicationDetails.generic_name;
  if (!searchQuery) {
    console.error('No search query available');
    return;
  }

  const cacheKey = `fda_${searchQuery.toLowerCase().replace(/\s+/g, '_')}`;
  const cachedData = localStorage.getItem(cacheKey);

  if (cachedData) {
    const { data, timestamp } = JSON.parse(cachedData);
    const isCacheValid = (Date.now() - timestamp) < 3600000; // 1 hour

    if (isCacheValid) {
      displayFDAData(data);
      return;
    }
  }

  try {
    elements.loadingSpinner?.classList.remove('hidden');
    
    // First try brand name search
    let apiUrl = `https://api.fda.gov/drug/label.json?search=openfda.brand_name:"${medicationDetails.brand_name}"&limit=1`;
    let response = await fetch(apiUrl);
    
    if (!response.ok) throw new Error(`FDA API error: ${response.status}`);
    
    let data = await response.json();

    // Fallback to generic name if no results
    if (!data.results?.length && medicationDetails.generic_name) {
      apiUrl = `https://api.fda.gov/drug/label.json?search=openfda.generic_name:"${medicationDetails.generic_name}"&limit=1`;
      response = await fetch(apiUrl);
      
      if (!response.ok) throw new Error(`FDA API error: ${response.status}`);
      
      data = await response.json();
    }

    if (data.results?.length) {
      const medicationData = data.results[0];
      localStorage.setItem(cacheKey, JSON.stringify({
        data: medicationData,
        timestamp: Date.now()
      }));
      displayFDAData(medicationData);
    } else {
      displayFDAData(null);
    }
  } catch (error) {
    console.error("FDA API Error:", error);
    displayFDAData(null, true);
  } finally {
    elements.loadingSpinner?.classList.add('hidden');
  }
};

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

  const cleanText = (text) => {
    if (!text) return '';
    if (Array.isArray(text)) return text[0]?.replace(/\[\d+\]/g, '') || '';
    return text.replace(/\[\d+\]/g, '');
  };

  elements.indications.textContent = cleanText(data.indications_and_usage) || defaultText.indications;
  elements.dosage.textContent = cleanText(data.dosage_and_administration) || defaultText.dosage;
  elements.warnings.textContent = cleanText(data.warnings) || defaultText.warnings;
  elements.contraindications.textContent = cleanText(data.contraindications) || defaultText.contraindications;
};

document.addEventListener('DOMContentLoaded', () => {
  fetchFDAData();
  

  elements.locateButton.addEventListener('click', () => {
    
    window.location.href = `../views/Map.php`;
});
  }
);