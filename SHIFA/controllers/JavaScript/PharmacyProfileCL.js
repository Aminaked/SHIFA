document.addEventListener('DOMContentLoaded', function() {
  const pharmacyData = JSON.parse(sessionStorage.getItem('medicationDetails'));
  const directionsBtn = document.getElementById('get-directions');
  const chatBtn = document.getElementById('start-chat');
  const makeRequestBtn = document.getElementById('make-request');

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

  if (makeRequestBtn) {
      makeRequestBtn.addEventListener('click', () => {
          console.log('Make request button clicked');
          showRequestForm();
      });
  }
  
  function showRequestForm() {
      console.log('showRequestForm called');
      // Create overlay
      const overlay = document.createElement('div');
      overlay.id = 'request-overlay';
      overlay.style.position = 'fixed';
      overlay.style.top = '0';
      overlay.style.left = '0';
      overlay.style.width = '100%';
      overlay.style.height = '100%';
      overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
      overlay.style.display = 'flex';
      overlay.style.justifyContent = 'center';
      overlay.style.alignItems = 'center';
      overlay.style.zIndex = '1000';

      // Create form container
      const formContainer = document.createElement('div');
      formContainer.style.backgroundColor = '#fff';
      formContainer.style.padding = '20px';
      formContainer.style.borderRadius = '8px';
      formContainer.style.width = '300px';
      formContainer.style.boxShadow = '0 2px 10px rgba(0,0,0,0.3)';
      formContainer.style.position = 'relative';

      // Close button
      const closeBtn = document.createElement('button');
      closeBtn.textContent = 'X';
      closeBtn.style.position = 'absolute';
      closeBtn.style.top = '10px';
      closeBtn.style.right = '10px';
      closeBtn.style.background = 'transparent';
      closeBtn.style.border = 'none';
      closeBtn.style.fontSize = '16px';
      closeBtn.style.cursor = 'pointer';
      closeBtn.addEventListener('click', () => {
          document.body.removeChild(overlay);
      });

      // Close overlay when clicking outside the form container
      overlay.addEventListener('click', (event) => {
          if (event.target === overlay) {
              document.body.removeChild(overlay);
          }
      });

      // Form element
      const form = document.createElement('form');
      form.id = 'request-form';

      // Client Name input
      const clientNameLabel = document.createElement('label');
      clientNameLabel.textContent = 'Client Name:';
      clientNameLabel.htmlFor = 'client-name';
      const clientNameInput = document.createElement('input');
      clientNameInput.type = 'text';
      clientNameInput.id = 'client-name';
      clientNameInput.name = 'client_name';
      clientNameInput.required = true;
      clientNameInput.style.width = '100%';
      clientNameInput.style.marginBottom = '10px';

      // Phone Number input
      const phoneLabel = document.createElement('label');
      phoneLabel.textContent = 'Phone Number:';
      phoneLabel.htmlFor = 'phone-number';
      const phoneInput = document.createElement('input');
      phoneInput.type = 'tel';
      phoneInput.id = 'phone-number';
      phoneInput.name = 'phone_number';
      phoneInput.required = true;
      phoneInput.style.width = '100%';
      phoneInput.style.marginBottom = '10px';

      // Medication Name input
      const medicationLabel = document.createElement('label');
      medicationLabel.textContent = 'Medication Name:';
      medicationLabel.htmlFor = 'medication-name';
      const medicationInput = document.createElement('input');
      medicationInput.type = 'text';
      medicationInput.id = 'medication-name';
      medicationInput.name = 'medication_name';
      medicationInput.required = true;
      medicationInput.style.width = '100%';
      medicationInput.style.marginBottom = '10px';

      // Quantity input
      const quantityLabel = document.createElement('label');
      quantityLabel.textContent = 'Quantity:';
      quantityLabel.htmlFor = 'quantity';
      const quantityInput = document.createElement('input');
      quantityInput.type = 'number';
      quantityInput.id = 'quantity';
      quantityInput.name = 'quantity';
      quantityInput.min = '1';
      quantityInput.required = true;
      quantityInput.style.width = '100%';
      quantityInput.style.marginBottom = '10px';

      // Client Notes input
      const clientNotesLabel = document.createElement('label');
      clientNotesLabel.textContent = 'Client Notes:';
      clientNotesLabel.htmlFor = 'client-notes';
      const clientNotesInput = document.createElement('textarea');
      clientNotesInput.id = 'client-notes';
      clientNotesInput.name = 'client_notes';
      clientNotesInput.rows = 3;
      clientNotesInput.style.width = '100%';
      clientNotesInput.style.marginBottom = '10px';

      // Submit button
      const submitBtn = document.createElement('button');
      submitBtn.type = 'submit';
      submitBtn.textContent = 'Send Request';
      submitBtn.style.width = '100%';
      submitBtn.style.padding = '10px';
      submitBtn.style.backgroundColor = '#007bff';
      submitBtn.style.color = '#fff';
      submitBtn.style.border = 'none';
      submitBtn.style.borderRadius = '4px';
      submitBtn.style.cursor = 'pointer';

      // Append inputs to form
      form.appendChild(clientNameLabel);
      form.appendChild(clientNameInput);
      form.appendChild(phoneLabel);
      form.appendChild(phoneInput);
      form.appendChild(medicationLabel);
      form.appendChild(medicationInput);
      form.appendChild(quantityLabel);
      form.appendChild(quantityInput);
      form.appendChild(clientNotesLabel);
      form.appendChild(clientNotesInput);
      form.appendChild(submitBtn);

      // Append close button and form to container
      formContainer.appendChild(closeBtn);
      formContainer.appendChild(form);

      // Append container to overlay
      overlay.appendChild(formContainer);

      // Append overlay to body
      document.body.appendChild(overlay);

      // Handle form submission
      form.addEventListener('submit', async (e) => {
          e.preventDefault();

          // Client-side validation
          const clientName = form.querySelector('#client-name').value.trim();
          const phoneNumber = form.querySelector('#phone-number').value.trim();
          const medicationName = form.querySelector('#medication-name').value.trim();
          const quantity = form.querySelector('#quantity').value.trim();

          if (!clientName || !phoneNumber || !medicationName || !quantity) {
              alert('Please fill in all required fields.');
              return;
          }

          const formData = new FormData(form);
          // Append pharmacy id and pharmacy name from pharmacyData
          formData.append('pharmacy_id', pharmacyData.pharmacy_id || '');
          formData.append('pharmacy_name', pharmacyData.pharmacy_name || '');
          try {
              const response = await fetch('../controllers/Add_Requests.php', {
                  method: 'POST',
                  body: formData
              });

              if (response.ok) {
                  // Remove form and show success message
                  formContainer.innerHTML = '';
                  const successMessage = document.createElement('div');
                  successMessage.style.textAlign = 'center';
                  successMessage.style.padding = '20px';
                  successMessage.innerHTML = `
                      <p>Request has been made successfully.</p>
                      <button id="view-requests-btn" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">View Requests</button>
                  `;
                  formContainer.appendChild(successMessage);

                  // Close overlay when clicking outside the success message
                  overlay.addEventListener('click', (event) => {
                      if (event.target === overlay) {
                          document.body.removeChild(overlay);
                      }
                  });

                  const viewRequestsBtn = document.getElementById('view-requests-btn');
                  viewRequestsBtn.addEventListener('click', () => {
                      window.location.href = '../views/Cl_Requests.php';
                  });
              } else {
                  alert('Failed to send request. Please try again.');
              }
          } catch (error) {
              console.error('Error sending request:', error);
              alert('An error occurred. Please try again.');
          }
      });
  }

  function showRequestForm() {
      // Create overlay
      const overlay = document.createElement('div');
      overlay.id = 'request-overlay';
      overlay.style.position = 'fixed';
      overlay.style.top = '0';
      overlay.style.left = '0';
      overlay.style.width = '100%';
      overlay.style.height = '100%';
      overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
      overlay.style.display = 'flex';
      overlay.style.justifyContent = 'center';
      overlay.style.alignItems = 'center';
      overlay.style.zIndex = '1000';

      // Create form container
      const formContainer = document.createElement('div');
      formContainer.style.backgroundColor = '#fff';
      formContainer.style.padding = '20px';
      formContainer.style.borderRadius = '8px';
      formContainer.style.width = '300px';
      formContainer.style.boxShadow = '0 2px 10px rgba(0,0,0,0.3)';
      formContainer.style.position = 'relative';

      // Close button
      const closeBtn = document.createElement('button');
      closeBtn.textContent = 'X';
      closeBtn.style.position = 'absolute';
      closeBtn.style.top = '10px';
      closeBtn.style.right = '10px';
      closeBtn.style.background = 'transparent';
      closeBtn.style.border = 'none';
      closeBtn.style.fontSize = '16px';
      closeBtn.style.cursor = 'pointer';
      closeBtn.addEventListener('click', () => {
          document.body.removeChild(overlay);
      });

      // Form element
      const form = document.createElement('form');
      form.id = 'request-form';

      // Client Name input
      const clientNameLabel = document.createElement('label');
      clientNameLabel.textContent = 'Client Name:';
      clientNameLabel.htmlFor = 'client-name';
      const clientNameInput = document.createElement('input');
      clientNameInput.type = 'text';
      clientNameInput.id = 'client-name';
      clientNameInput.name = 'client_name';
      clientNameInput.required = true;
      clientNameInput.style.width = '100%';
      clientNameInput.style.marginBottom = '10px';

      // Phone Number input
      const phoneLabel = document.createElement('label');
      phoneLabel.textContent = 'Phone Number:';
      phoneLabel.htmlFor = 'phone-number';
      const phoneInput = document.createElement('input');
      phoneInput.type = 'tel';
      phoneInput.id = 'phone-number';
      phoneInput.name = 'phone_number';
      phoneInput.required = true;
      phoneInput.style.width = '100%';
      phoneInput.style.marginBottom = '10px';

      // Medication Name input
      const medicationLabel = document.createElement('label');
      medicationLabel.textContent = 'Medication Name:';
      medicationLabel.htmlFor = 'medication-name';
      const medicationInput = document.createElement('input');
      medicationInput.type = 'text';
      medicationInput.id = 'medication-name';
      medicationInput.name = 'medication_name';
      medicationInput.required = true;
      medicationInput.style.width = '100%';
      medicationInput.style.marginBottom = '10px';

      // Quantity input
      const quantityLabel = document.createElement('label');
      quantityLabel.textContent = 'Quantity:';
      quantityLabel.htmlFor = 'quantity';
      const quantityInput = document.createElement('input');
      quantityInput.type = 'number';
      quantityInput.id = 'quantity';
      quantityInput.name = 'quantity';
      quantityInput.min = '1';
      quantityInput.required = true;
      quantityInput.style.width = '100%';
      quantityInput.style.marginBottom = '10px';

      // Client Notes input
      const clientNotesLabel = document.createElement('label');
      clientNotesLabel.textContent = 'Client Notes:';
      clientNotesLabel.htmlFor = 'client-notes';
      const clientNotesInput = document.createElement('textarea');
      clientNotesInput.id = 'client-notes';
      clientNotesInput.name = 'client_notes';
      clientNotesInput.rows = 3;
      clientNotesInput.style.width = '100%';
      clientNotesInput.style.marginBottom = '10px';

      // Submit button
      const submitBtn = document.createElement('button');
      submitBtn.type = 'submit';
      submitBtn.textContent = 'Send Request';
      submitBtn.style.width = '100%';
      submitBtn.style.padding = '10px';
      submitBtn.style.backgroundColor = '#007bff';
      submitBtn.style.color = '#fff';
      submitBtn.style.border = 'none';
      submitBtn.style.borderRadius = '4px';
      submitBtn.style.cursor = 'pointer';

      // Append inputs to form
      form.appendChild(clientNameLabel);
      form.appendChild(clientNameInput);
      form.appendChild(phoneLabel);
      form.appendChild(phoneInput);
      form.appendChild(medicationLabel);
      form.appendChild(medicationInput);
      form.appendChild(quantityLabel);
      form.appendChild(quantityInput);
      form.appendChild(clientNotesLabel);
      form.appendChild(clientNotesInput);
      form.appendChild(submitBtn);

      // Append close button and form to container
      formContainer.appendChild(closeBtn);
      formContainer.appendChild(form);

      // Append container to overlay
      overlay.appendChild(formContainer);

      // Append overlay to body
      document.body.appendChild(overlay);

      // Handle form submission
      form.addEventListener('submit', async (e) => {
          e.preventDefault();

          const formData = new FormData(form);
          // Append pharmacy id and pharmacy name from pharmacyData
          formData.append('pharmacy_id', pharmacyData.pharmacy_id || '');
          formData.append('pharmacy_name', pharmacyData.pharmacy_name || '');
          try {
              const response = await fetch('../controllers/Add_Requests.php', {
                  method: 'POST',
                  body: formData
              });

              if (response.ok) {
                  // Remove form and show success message
                  formContainer.innerHTML = '';
                  const successMessage = document.createElement('div');
                  successMessage.style.textAlign = 'center';
                  successMessage.style.padding = '20px';
                  successMessage.innerHTML = `
                      <p>Request has been made successfully.</p>
                      <button id="view-requests-btn" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">View Requests</button>
                  `;
                  formContainer.appendChild(successMessage);

                  const viewRequestsBtn = document.getElementById('view-requests-btn');
                  viewRequestsBtn.addEventListener('click', () => {
                      window.location.href = '../views/Cl_Requests.php';
                  });
              } else {
                  alert('Failed to send request. Please try again.');
              }
          } catch (error) {
              console.error('Error sending request:', error);
              alert('An error occurred. Please try again.');
          }
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