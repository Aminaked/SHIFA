document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('reservationsBody');
  if (!container) {
    console.error('Reservations container element not found');
    return;
  }

  // Create modal overlay elements for approve and cancel notes
  const modalOverlay = document.createElement('div');
  modalOverlay.style.position = 'fixed';
  modalOverlay.style.top = '0';
  modalOverlay.style.left = '0';
  modalOverlay.style.width = '100vw';
  modalOverlay.style.height = '100vh';
  modalOverlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
  modalOverlay.style.display = 'none';
  modalOverlay.style.justifyContent = 'center';
  modalOverlay.style.alignItems = 'center';
  modalOverlay.style.zIndex = '1000';

  const modalForm = document.createElement('form');
  modalForm.style.background = 'white';
  modalForm.style.padding = '20px';
  modalForm.style.borderRadius = '8px';
  modalForm.style.maxWidth = '400px';
  modalForm.style.width = '90%';
  modalForm.style.boxShadow = '0 2px 10px rgba(0,0,0,0.3)';
  modalForm.innerHTML = `
    <h2 id="modalTitle">Confirm Action</h2>
    <label for="pharmacyNote">Pharmacy Note:</label>
    <textarea id="pharmacyNote" name="pharmacyNote" rows="4" style="width: 100%;"></textarea>
    <div id="dueDateContainer" style="margin-top: 10px; display: none;">
      <label for="dueDate">Due Date:</label>
      <input type="date" id="dueDate" name="dueDate" style="width: 100%;" />
    </div>
    <div style="margin-top: 15px; text-align: right;">
      <button type="submit" style="margin-right: 10px;">Submit</button>
      <button type="button" id="cancelModalBtn">Cancel</button>
    </div>
  `;

  modalOverlay.appendChild(modalForm);
  document.body.appendChild(modalOverlay);

  let currentReservationId = null;
  let currentAction = null; // 'approve' or 'cancel'

  function showModal(reservationId, action, existingNote, existingDueDate) {
    currentReservationId = reservationId;
    currentAction = action;
    const modalTitle = modalForm.querySelector('#modalTitle');
    modalTitle.textContent = action === 'approve' ? 'Approve Reservation' : 'Cancel Reservation';
    modalForm.pharmacyNote.value = existingNote || '';
    const dueDateContainer = modalForm.querySelector('#dueDateContainer');
    const dueDateInput = modalForm.querySelector('#dueDate');
    if (action === 'approve') {
      dueDateContainer.style.display = 'block';
      dueDateInput.required = true;
      dueDateInput.value = existingDueDate ? existingDueDate.split('T')[0] : '';
    } else {
      dueDateContainer.style.display = 'none';
      dueDateInput.required = false;
      dueDateInput.value = '';
    }
    modalOverlay.style.display = 'flex';
  }

  function hideModal() {
    currentReservationId = null;
    currentAction = null;
    modalOverlay.style.display = 'none';
  }

  document.getElementById('cancelModalBtn').addEventListener('click', (e) => {
    e.preventDefault();
    hideModal();
  });

  modalForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!currentReservationId || !currentAction) return;

    const note = modalForm.pharmacyNote.value.trim();
    const dueDate = modalForm.querySelector('#dueDate').value;
    const status = currentAction === 'approve' ? 'approved' : 'cancelled';

    if (currentAction === 'approve' && !dueDate) {
      alert('Please enter a due date.');
      return;
    }

    try {
      const response = await fetch('../controllers/Ph_Update_Reservations.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ reservation_id: currentReservationId, pharmacy_note: note, status: status, due_date: dueDate })
      });

      const result = await response.json();

      if (response.ok && result.success) {
        alert(`Reservation ${status} successfully.`);
        hideModal();
        loadReservations();
      } else {
        alert(`Failed to ${currentAction} reservation: ` + (result.message || 'Unknown error'));
      }
    } catch (error) {
      alert(`Error ${currentAction} reservation: ` + error.message);
    }
  });

  function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    if (isNaN(date)) return 'N/A';
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
  }

  async function loadReservations() {
    try {
      const response = await fetch('../controllers/Ph_Get_Reservations.php');
      if (!response.ok) throw new Error('Failed to fetch reservations');

      const data = await response.json();
      if (!data.reservations || !Array.isArray(data.reservations)) {
        container.innerHTML = '<p>No reservations found.</p>';
        return;
      }

      // Build table with approve and cancel buttons
      let html = `
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr>
              <th style="border: 1px solid #ddd; padding: 8px;">Product Name</th>
              <th style="border: 1px solid #ddd; padding: 8px;">Client Name</th>
              <th style="border: 1px solid #ddd; padding: 8px;">Quantity</th>
              <th style="border: 1px solid #ddd; padding: 8px;">Status</th>
              <th style="border: 1px solid #ddd; padding: 8px;">Reservation Date</th>
              <th style="border: 1px solid #ddd; padding: 8px;">Pharmacy Note</th>
              <th style="border: 1px solid #ddd; padding: 8px;">Actions</th>
            </tr>
          </thead>
          <tbody>
      `;

      data.reservations.forEach(reservation => {
        const isCancelled = reservation.status.toLowerCase() === 'cancelled';
        const isConfirmed = reservation.status.toLowerCase() === 'approved';

        html += `
          <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">${reservation.product_name}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${reservation.client_name}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${reservation.quantity}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${reservation.status}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${formatDate(reservation.reservation_date)}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${reservation.pharmacy_note || ''}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">
              <button class="approve-btn" data-id="${reservation.reservation_id}" ${isCancelled || isConfirmed ? 'disabled' : ''}>Approve</button>
              <button class="cancel-btn" data-id="${reservation.reservation_id}" ${isCancelled ? 'disabled' : ''}>Cancel</button>
            </td>
          </tr>
        `;
      });

      html += '</tbody></table>';
      container.innerHTML = html;

      // Add event listeners for approve buttons
      container.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const reservationId = btn.getAttribute('data-id');
          const reservation = data.reservations.find(r => r.reservation_id == reservationId);
          showModal(reservationId, 'approve', reservation.pharmacy_note, reservation.due_date);
        });
      });

      // Add event listeners for cancel buttons
      container.querySelectorAll('.cancel-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const reservationId = btn.getAttribute('data-id');
          showModal(reservationId, 'cancel', '');
        });
      });

    } catch (error) {
      container.innerHTML = '<p>Error loading reservations.</p>';
      console.error('Error loading reservations:', error);
    }
  }

  loadReservations();
});
