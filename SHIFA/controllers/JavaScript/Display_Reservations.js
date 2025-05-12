document.addEventListener('DOMContentLoaded', function () {

const medDetails = JSON.parse(sessionStorage.getItem('medicationDetails'));

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

async function cancelReservation(reservationId) {
  console.log('cancelReservation called with id:', reservationId);
  try {
    const response = await fetch(`http://localhost/SHIFA/SHIFA/controllers/Update_Reservations.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ reservation_id: reservationId, status: 'cancelled' })
    });

    console.log('Response status:', response.status);
    const result = await response.json();
    console.log('Response JSON:', result);

    if (response.ok && result.success) {
      alert('Reservation cancelled successfully.');
      loadReservations();
    } else {
      alert('Failed to cancel reservation: ' + (result.message || 'Unknown error'));
    }
  } catch (error) {
    alert('Error cancelling reservation: ' + error.message);
  }
}

async function loadReservations() {
    try {
        const response = await fetch(`http://localhost/SHIFA/SHIFA/controllers/Get_Reservations.php`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        const tbody = document.getElementById('reservationsBody');
        if (!tbody) {
            console.warn("Element with id 'reservationsBody' not found in the DOM.");
            return;
        }
        tbody.innerHTML = ''; // Clear existing content

        data.reservations.forEach(reservation => {
            const isCancelled = reservation.status.toLowerCase() === 'cancelled';
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${reservation.product_name}</td>
                <td>${reservation.pharmacy_name}</td>
                <td>${reservation.quantity}</td>       
                <td>${reservation.price}</td>       
                <td>${reservation.formatted_reservation_date}</td>
                <td class="status-${reservation.status}">${capitalizeFirstLetter(reservation.status)}</td>
                <td>${reservation.formatted_due_date || 'N/A'}</td>
                <td>${reservation.pharmacy_notes || ''}</td>
                <td>
                  <button class="cancel-btn" data-id="${reservation.reservation_id}" ${isCancelled ? 'disabled' : ''}>Cancel</button>
                </td>
            `;
            tbody.appendChild(row);
        });

        // Add event listeners for cancel buttons
        tbody.querySelectorAll('.cancel-btn').forEach(btn => {
          btn.addEventListener('click', () => {
            const reservationId = btn.getAttribute('data-id');
            console.log('Cancel button clicked for id:', reservationId);
            cancelReservation(reservationId);
          });
        });

    } catch (error) {
        console.error('Error loading reservations:', error);
        alert('Failed to load reservations. Please try again.');
    }
}
loadReservations() ;
});
