document.addEventListener('DOMContentLoaded', function() {
  const pharmacyData = JSON.parse(sessionStorage.getItem('medicationDetails'));
  const directionsBtn = document.getElementById('get-directions');
  const chatBtn = document.getElementById('start-chat');
  
  if (!pharmacyData) {
      console.error('No pharmacy data found in session storage');
      displayError();
      return;
  }
  
  displayPharmacyInfo(pharmacyData);
  function displayPharmacyInfo(data) {
    try {
        const profileBox = document.querySelector('.profile-box');
        const pharmacyHeader = document.createElement('div');
        pharmacyHeader.className = 'pharmacy-header';
        
        pharmacyHeader.innerHTML = `
            <img src="../uploads/${data.profile_photo || 'default.jpg'}" 
                 class="pharmacy-logo"
                 alt="${data.pharmacy_name}" 
                 onerror="this.onerror=null;this.src='../public/images/client.jpg'">
            <div class="pharmacy-info">
                <h1 class="pharmacy-name">${escapeHtml(data.pharmacy_name) || 'Pharmacy Name'}</h1>
                <div class="pharmacy-meta">
                    
                    <span class="pharmacy-distance">${data.distance || '2.5'}  away</span>
                </div>
            </div>
       ` ;
        
        profileBox.parentNode.insertBefore(pharmacyHeader, profileBox);
        document.getElementById('pharmacy_name').textContent = data.pharmacy_name || 'N/A';
        document.getElementById('address').textContent = data.address;
        document.getElementById('phone_number').textContent = data.phone_number;
        document.getElementById('email').textContent = data.email;

    } catch (error) {
        console.error('Error displaying pharmacy info:', error);
    }
  }

  if (directionsBtn) {
      directionsBtn.addEventListener('click', () => {
          window.location.href = '../views/Map.php';
      });
  }
  
  if (chatBtn) {
      chatBtn.addEventListener('click', () => {
          window.location.href = '../views/ClientChatPage.php';
      });
  }
});

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

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