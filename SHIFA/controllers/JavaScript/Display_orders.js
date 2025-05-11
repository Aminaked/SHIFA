document.addEventListener('DOMContentLoaded', function () {

  function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  async function loadOrders() {
    try {
      const response = await fetch(`http://localhost/SHIFA/SHIFA/controllers/Get_Orders.php`);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      console.log('Orders data:', data);
      const tbody = document.getElementById('ordersBody');
      if (!tbody) {
        console.warn("Element with id 'ordersBody' not found in the DOM.");
        return;
      }
      tbody.innerHTML = ''; // Clear existing content

      data.reservations.forEach(order => {
        const row = document.createElement('tr');

        // Disable cancel button if status is cancelled or completed
        const isCancelable = !(order.status.toLowerCase() === 'cancelled' || order.status.toLowerCase() === 'completed');

        row.innerHTML = `
          <td>${order.product_name}</td>
          <td>${order.pharmacy_name}</td>
          <td>${order.quantity}</td>       
          <td>${order.price}</td>       
          <td>${order.formatted_order_date || 'N/A'}</td>
          <td class="status-${order.status}">${capitalizeFirstLetter(order.status)}</td>
          <td>${order.formatted_due_date || 'N/A'}</td>
          <td>${order.pharmacy_notes || ''}</td>
          <td>
            <button class="cancel-button" ${isCancelable ? '' : 'disabled'} data-order-id="${order.order_id}">
              Cancel
            </button>
          </td>
        `;
        tbody.appendChild(row);
      });

      // Add event listeners for cancel buttons
      document.querySelectorAll('.cancel-button').forEach(button => {
        button.addEventListener('click', async (event) => {
          const orderId = event.target.getAttribute('data-order-id');
          if (!orderId) return;

          if (!confirm('Are you sure you want to cancel this order?')) return;

          try {
            const cancelResponse = await fetch('../controllers/Cl_Cancel_Order.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ order_id: orderId })
            });

            const result = await cancelResponse.json();

            if (cancelResponse.ok && result.success) {
              alert('Order cancelled successfully.');
              // Reload orders to update UI
              loadOrders();
            } else {
              alert('Failed to cancel order: ' + (result.message || 'Unknown error'));
            }
          } catch (error) {
            alert('Error cancelling order: ' + error.message);
          }
        });
      });

    } catch (error) {
      console.error('Error loading orders:', error);
      alert('Failed to load orders. Please try again.');
    }
  }

  loadOrders();
});
