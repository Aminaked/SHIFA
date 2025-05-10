document.addEventListener('DOMContentLoaded', function () {

const medDetails = JSON.parse(sessionStorage.getItem('medicationDetails'));

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
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
            `;
            tbody.appendChild(row);
        });

    } catch (error) {
        console.error('Error loading reservations:', error);
        alert('Failed to load reservations. Please try again.');
    }
}
loadReservations() ;
});
