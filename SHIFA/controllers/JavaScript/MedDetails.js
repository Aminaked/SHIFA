
document.addEventListener('DOMContentLoaded', function () {

  const medData = JSON.parse(sessionStorage.getItem('medicationDetails'));
  if (!medData) {
    window.location.href = '../views/CLHomepage.php';
    return;
  }


  const container = document.querySelector('.details');
  container.innerHTML = `
  <div class="medication-layout">
    <div class="medication-content">
      <div class="med-header">
        <h1 class="med-title" id="brand-name">${escapeHtml(medData.brand_name)}</h1>
        
        <div class="pharmacy-header">
          <img src="../uploads/${medData.profile_photo}" 
            class="pharmacy-logo"
            alt="${medData.pharmacy_name}" 
            onerror="this.onerror=null;this.src='../public/images/client.jpg'">
          <span class="pharmacy-name">${escapeHtml(medData.pharmacy_name)}</span>
        </div>
        
        <div class="address-line">
          <div class="address-container">
            <span class="address" id="address">${escapeHtml(medData.address)}</span>
            <span class="distance-badge" id="distance">${escapeHtml(medData.distance)}  away</span>
          </div>
          <button class="contact-button"> View Profile </button>
        </div>
      </div>

      <div class="stock-info">
        <p class="stock-status">
          <span class="label">Availability:</span> 
          <span id="stock">${escapeHtml(medData.stock)}</span>
        </p>
             <p class="stock-status">
          <span class="label">Availability:</span> 
          <span id="stock">${escapeHtml(medData.price)}</span>
        </p>
        <p class="generic-name">
          <span class="label">Generic Name:</span> 
          <span id="generic-name"></span>
        </p>
        
      </div>

      <div class="medical-details">
        <h3 class="section-title"><i class="fas fa-info-circle"></i> About this medication</h3>
        
        <div class="detail-section">
          <h3 class="section-title"><i class="fas fa-hand-holding-medical"></i> Uses</h3>
          <p class="detail-text" id="indications">Loading...</p>
        </div>
        
        <div class="detail-section">
          <h3 class="section-title"><i class="fas fa-prescription-bottle-alt"></i> Dosage</h3>
          <p class="detail-text" id="dosage">Loading...</p>
        </div>
        
        <div class="detail-section">
          <h3 class="section-title"><i class="fas fa-exclamation-triangle"></i> Warnings</h3>
          <p class="detail-text warning-box" id="warnings">Loading...</p>
        </div>
      </div>
    </div>
    
    <div class="reservation-card">
      <div class="card-content">
        <p class="reservation-text">Available for immediate reservation or order</p>
      
          <button class="reservation-button">
          <i class="fas fa-calendar-check"></i> Reserve Now
        </button>
        <button class="reservation-button" id="order-button">
          <i class="fa fa-shopping-cart"></i> Order
        </button>
      </div>
    </div>
  </div>
`;
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
if (medData && medData.brand_name) {
  const brandName = extractBrandName(medData.brand_name);
  if (!brandName) return; // Prevent empty API calls

  const fetchGenericName = async () => {
    try {
      // Fetch generic name from FDA API by brand name
      const response = await fetch(
        `https://api.fda.gov/drug/label.json?search=openfda.brand_name:"${encodeURIComponent(brandName)}"&limit=1`
      );

      if (!response.ok) throw new Error(`FDA API fetch error: ${response.status}`);

      const data = await response.json();
      const results = data.results?.[0];
      const genericName = results?.openfda?.generic_name?.[0] || null;

      if (genericName) {
        document.getElementById('generic-name').textContent = genericName;
        // After setting generic name, fetch FDA data for other details
        fetchFDAData(medData, brandName);
      } else {
        // Fallback to RxNorm API if FDA API has no generic name
        await fetchGenericNameRxNorm(brandName, medData);
      }
    } catch (error) {
      console.error('FDA API Error:', error);
      // Fallback to RxNorm API on error
      await fetchGenericNameRxNorm(brandName, medData);
    }
  };

  async function fetchGenericNameRxNorm(brandName, medData) {
    try {
      // Step 1: Fetch RXCUI for the brand name
      const rxcuiRes = await fetch(
        `https://rxnav.nlm.nih.gov/REST/rxcui.json?name=${encodeURIComponent(brandName)}&search=2`
      );

      if (!rxcuiRes.ok) throw new Error(`RxNorm RXCUI fetch error: ${rxcuiRes.status}`);

      const rxcuiData = await rxcuiRes.json();
      const rxcui = rxcuiData.idGroup?.rxnormId?.[0];

      if (!rxcui) {
        document.getElementById('generic-name').textContent = 'Not available';
        fetchFDAData(medData, brandName);
        return;
      }

      // Step 2: Fetch generic name using RXCUI
      let genericName = null;
      try {
        // Try property endpoint
        const genericRes = await fetch(
          `https://rxnav.nlm.nih.gov/REST/rxcui/${rxcui}/property.json?propName=GENERIC_NAME`
        );

        if (!genericRes.ok) throw new Error(`RxNorm generic name fetch error: ${genericRes.status}`);

        const genericData = await genericRes.json();
        genericName = genericData.propConceptGroup?.propConcept?.[0]?.propValue;

        // If genericName is null or empty, try related concepts fallback
        if (!genericName) {
          throw new Error('Generic name property empty, trying related concepts fallback');
        }
      } catch (error) {
        console.warn('Failed to fetch generic name property or empty, trying allProperties endpoint...', error);
        // Try allProperties endpoint
        try {
          // Try related concepts endpoint with tty=IN for ingredient name (generic name) first as requested
          const relatedRes = await fetch(
            `https://rxnav.nlm.nih.gov/REST/rxcui/${rxcui}/related.json?tty=IN`
          );
            if (relatedRes.ok) {
              const relatedData = await relatedRes.json();
              genericName = relatedData.relatedGroup?.conceptGroup?.[0]?.conceptProperties?.[0]?.name;
            }
          // If no genericName from related concepts, try allProperties endpoint
          if (!genericName) {
            const allPropsRes = await fetch(
              `https://rxnav.nlm.nih.gov/REST/rxcui/${rxcui}/allProperties.json?prop=all`
            );
            if (allPropsRes.ok) {
              const allPropsData = await allPropsRes.json();
              const props = allPropsData.propConceptGroup?.propConcept || [];
              const genericProp = props.find(p => p.propName === 'GENERIC_NAME');
              if (genericProp) {
                genericName = genericProp.propValue;
              }
            }
          }
        } catch (err) {
          console.warn('Failed to fetch related concepts or allProperties for generic name fallback...', err);
        }
      }

      document.getElementById('generic-name').textContent = genericName || 'Not available';

      // After setting generic name, fetch FDA data
      fetchFDAData(medData, brandName);

    } catch (error) {
      console.error('RxNorm API Error:', error);
      document.getElementById('generic-name').textContent = 'Not available';
      fetchFDAData(medData, brandName);
    }
  }

  fetchGenericName();
}
async function fetchFDAData(medData, brandName) {
  try {
    console.log('fetchFDAData brandName:', brandName);
    if (!brandName) return showDefaultData();

    // First try brand name search
    let response = await fetch(
      `https://api.fda.gov/drug/label.json?search=openfda.brand_name:"${encodeURIComponent(brandName)}"&limit=1`
    );

    // If 404, try generic name search
    if (response.status === 404) {
      const genericName = document.getElementById('generic-name').textContent;
      response = await fetch(
        `https://api.fda.gov/drug/label.json?search=openfda.generic_name:"${encodeURIComponent(genericName)}"&limit=1`
      );
    }

    if (!response.ok) throw new Error('Failed to fetch FDA data');

    const data = await response.json();
    const results = data.results?.[0];

    if (!results) return showDefaultData();

    // Extract and update indications, dosage, warnings
    updateElement('indications', results.indications_and_usage?.[0], 'No medication information available');
    updateElement('dosage', results.dosage_and_administration?.[0], 'Dosage information not available');
    updateElement('warnings', results.warnings?.[0], 'No warnings data found');

  } catch (error) {
    console.error("FDA API Error:", error);
    showDefaultData();
  }
}


  function updateElement(id, content, defaultText) {
    const element = document.getElementById(id);
    element.textContent = content ? cleanText(content) : defaultText;
  }

  function cleanText(text) {
    return text.replace(/\[\d+\]/g, '').replace(/\s+/g, ' ').trim();
  }

  function escapeHtml(unsafe) {
    return unsafe?.toString()?.replace(/[&<>"']/g, '') || '';
  }

  function showDefaultData() {
    updateElement('indications', null, 'No medication information available');
    updateElement('warnings', null, 'No warnings data found');
    updateElement('dosage', null, 'Dosage information not available');
  }


  function setupEventListeners() {

    document.querySelector('.reservation-button')?.addEventListener('click', function () {
      window.location.href = '../views/Cl_Reservations.php';
    });
document.querySelector('#reservation-button')?.addEventListener('click', function () {
      window.location.href = '../views/Cl_Orders.php';
    });

    document.querySelector('.contact-button')?.addEventListener('click', function () {
      window.location.href = `../views/PharmacyProfileCL.php?id=${encodeURIComponent(medData.pharmacy_id)}`;
    });
  }

  fetchFDAData(medData.brandName);
  setupEventListeners();
});


function toggleMenu(event) {
  event.preventDefault();
  const menu = document.getElementById("menuDropdown");
  menu.style.display = menu.style.display === "block" ? "none" : "block";
}

document.addEventListener("click", function (event) {
  const menu = document.getElementById("menuDropdown");
  const userIcon = document.querySelector(".login");

  if (menu && userIcon) {
    if (!userIcon.contains(event.target) && !menu.contains(event.target)) {
      menu.style.display = "none";
    }
  }
});
