
async function loadReservations() {
    try {
        const response = await fetch(`http://localhost/SHIFA/SHIFA/controllers/Get_Reservations.php`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        const tbody = document.getElementById('reservationsBody');
        tbody.innerHTML = ''; // Clear existing content

        data.reservations.forEach(reservation => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${reservation.product_name}</td>
                <td>${reservation.pharmacy_name}</td>
               
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

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Load reservations when page loads
document.addEventListener('DOMContentLoaded', loadReservations);
