document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('ordersBody');
  if (!container) {
    console.error('Orders container element not found');
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
    <div style="margin-top: 15px; text-align: right;">
      <button type="submit" style="margin-right: 10px;">Submit</button>
      <button type="button" id="cancelModalBtn">Cancel</button>
    </div>
  `;

  modalOverlay.appendChild(modalForm);
  document.body.appendChild(modalOverlay);

  let currentOrderId = null;
  let currentAction = null; // 'approve' or 'cancel'

  function showModal(orderId, action, existingNote) {
    currentOrderId = orderId;
    currentAction = action;
    const modalTitle = modalForm.querySelector('#modalTitle');
    modalTitle.textContent = action === 'approve' ? 'Approve Order' : 'Cancel Order';
    modalForm.pharmacyNote.value = existingNote || '';
    modalOverlay.style.display = 'flex';
  }

  function hideModal() {
    currentOrderId = null;
    currentAction = null;
    modalOverlay.style.display = 'none';
  }

  document.getElementById('cancelModalBtn').addEventListener('click', (e) => {
    e.preventDefault();
    hideModal();
  });

  modalForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!currentOrderId || !currentAction) return;

    const note = modalForm.pharmacyNote.value.trim();
    const status = currentAction === 'approve' ? 'approved' : 'cancelled';

    try {
      const response = await fetch('../controllers/Ph_Update_Orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: currentOrderId, pharmacy_note: note, status: status })
      });

      const result = await response.json();

      if (response.ok && result.success) {
        alert(`Order ${status} successfully.`);
        hideModal();
        loadOrders();
      } else {
        alert(`Failed to ${currentAction} order: ` + (result.message || 'Unknown error'));
      }
    } catch (error) {
      alert(`Error ${currentAction} order: ` + error.message);
    }
  });

  function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    if (isNaN(date)) return 'N/A';
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
  }

  async function loadOrders() {
    try {
      const response = await fetch('../controllers/Ph_Get_Orders.php');
      if (!response.ok) throw new Error('Failed to fetch orders');

      const data = await response.json();
      if (!data.orders || !Array.isArray(data.orders)) {
        container.innerHTML = '<p>No orders found.</p>';
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
              <th style="border: 1px solid #ddd; padding: 8px;">Price</th>
              <th style="border: 1px solid #ddd; padding: 8px;">Order Date</th>
              <th style="border: 1px solid #ddd; padding: 8px;">Due Date</th>
              <th style="border: 1px solid #ddd; padding: 8px;">Status</th>
           <th style="border: 1px solid #ddd; padding: 8px;">client Notes</th>
              <th style="border: 1px solid #ddd; padding: 8px;">Pharmacy Notes</th>
              <th style="border: 1px solid #ddd; padding: 8px;">Actions</th>
            </tr>
          </thead>
          <tbody>
      `;

      data.orders.forEach(order => {
        const isCancelled = order.status.toLowerCase() === 'cancelled';
        const isConfirmed = order.status.toLowerCase() === 'approved';

        html += `
          <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">${order.product_name}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${order.client_name}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${order.quantity}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${order.price}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${formatDate(order.order_date)}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${formatDate(order.due_date)}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${order.status}</td>
            
             <td style="border: 1px solid #ddd; padding: 8px;">${order.client_notes || ''}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">${order.pharmacy_notes || ''}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">
              <button class="approve-btn" data-id="${order.order_id}" ${isCancelled || isConfirmed ? 'disabled' : ''}>Approve</button>
              <button class="cancel-btn" data-id="${order.order_id}" ${isCancelled ? 'disabled' : ''}>Cancel</button>
            </td>
          </tr>
        `;
      });

      html += '</tbody></table>';
      container.innerHTML = html;

      // Add event listeners for approve buttons
      container.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const orderId = btn.getAttribute('data-id');
          const order = data.orders.find(o => o.order_id == orderId);
          showModal(orderId, 'approved', order.pharmacy_note);
        });
      });

      // Add event listeners for cancel buttons
      container.querySelectorAll('.cancel-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const orderId = btn.getAttribute('data-id');
          showModal(orderId, 'cancel', '');
        });
      });

    } catch (error) {
      container.innerHTML = '<p>Error loading orders.</p>';
      console.error('Error loading orders:', error);
    }
  }

  loadOrders();
});
