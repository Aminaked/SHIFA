

document.addEventListener('DOMContentLoaded', function() {
  
  const medData = JSON.parse(sessionStorage.getItem('medicationDetails'));
  if (!medData) {
      window.location.href = '../views/CLHomepage.php';
      return;
  }

  
  const container = document.querySelector('.details');
container.innerHTML =  `
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
        <p class="generic-name">
          <span class="label">Generic Name:</span> 
          <span id="generic-name">${escapeHtml(medData.generic_name)}</span>
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
        <button class="reservation-button">
          <i class="fa fa-shopping-cart"></i> Order
        </button>
      </div>
    </div>
  </div>
`;


  
  async function fetchFDAData() {
      try {
          const searchQuery = medData.brand_name || medData.generic_name;
          const response = await fetch(`https://api.fda.gov/drug/label.json?search=openfda.brand_name:"${encodeURIComponent(searchQuery)}"&limit=1`);
          
          if (!response.ok) throw new Error('Failed to fetch FDA data');
          
          const data = await response.json();

          if (data.results?.length) {
              const result = data.results[0];
              updateElement('indications', result.indications_and_usage?.[0], 'No usage information available');
              updateElement('warnings', result.warnings?.[0], 'No warnings listed');
                updateElement('dosage', result.dosage_and_administration?.[0], 'Dosage not specified');
            } else {
                showDefaultData();
            }
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
        
        document.querySelector('.reservation-button')?.addEventListener('click', function() {
            window.location.href =` ../views/ReservationPage.php?med=${encodeURIComponent(medData.brand_name)}&pharmacy=${encodeURIComponent(medData.pharmacy_id)}`;
        });

        
        document.querySelector('.contact-button')?.addEventListener('click', function() {
            window.location.href = `../views/PharmacyProfileCL.php?id=${encodeURIComponent(medData.pharmacy_id)}`;
        });
    }

   
    fetchFDAData();
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
