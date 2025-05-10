 document.addEventListener('DOMContentLoaded', function () {

const medDetails = JSON.parse(sessionStorage.getItem('medicationDetails'));



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
        const tbody = document.getElementById('ordersBody');
        console.log('Orders data:', data);
        if (!tbody) {
            console.warn("Element with id 'ordersBody' not found in the DOM.");
            return;
        }
        tbody.innerHTML = ''; // Clear existing content

        data.orders.forEach(orders => {
            const row = document.createElement('tr');
            row.innerHTML = `
                  <td>${orders.product_name}</td>
                <td>${orders.pharmacy_name}</td>
               <td>${orders.quantity}</td>       
                <td>${orders.price}</td>       
              <td>${orders.formatted_order_date}</td>
                <td class="status-${orders.status}">${capitalizeFirstLetter(orders.status)}</td>
                <td>${orders.formatted_due_date || 'N/A'}</td>
                <td>${orders.pharmacy_notes || ''}</td>
                 <td>
            <button class="cancel-button" ${isCancelable ? '' : 'disabled'} data-order-id="${order.order_id}">
              Cancel
            </button>
          </td
            `;
            tbody.appendChild(row);
        });

    } catch (error) {
        console.error('Error loading orders:', error);
        alert('Failed to load orders. Please try again.');
    }
}


loadOrders() ;
});
