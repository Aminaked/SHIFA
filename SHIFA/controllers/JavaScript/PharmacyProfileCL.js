document.addEventListener('DOMContentLoaded', function() {
  const pharmacyData = JSON.parse(sessionStorage.getItem('medicationDetails'));
  const directionsBtn = document.getElementById('get-directions');
  const chatBtn = document.getElementById('start-chat');
  console.log('Directions button found:', directionsBtn); // Check if the button is found
  if (!pharmacyData) {
      console.error('No pharmacy data found in session storage');
      displayError();
      return;
  }
  displayPharmacyInfo(pharmacyData);

  function displayPharmacyInfo(data) {
    try {
        document.getElementById('pharmacy_name').textContent = data.pharmacy_name || 'Pharmacy Name Not Available';
        document.getElementById('address').textContent = data.address;
        document.getElementById('phone_number').textContent = data.phone_number;
        document.getElementById('email').textContent = data.email;
     
    } catch (error) {
        console.error('Error displaying pharmacy info:', error);
        
    }
  }


  if (!directionsBtn) {
      console.error('Directions button not found in the DOM');
  } else {
      directionsBtn.addEventListener('click', () => {
          console.log('Get Directions button clicked');
          window.location.href = `../views/Map.php`;
      });
  }
  if (!chatBtn) {
    console.error('chat button not found in the DOM');
} else {
    chatBtn.addEventListener('click', () => {
        console.log('chat button clicked');
        window.location.href = `../views/ClientChatPage.php`;
    });
}
});
