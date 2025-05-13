document.addEventListener('DOMContentLoaded', function () {
    const requestsBody = document.getElementById('requestsBody');

    async function fetchRequests() {
        try {
            const response = await fetch('../controllers/Get_Requests.php');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            populateRequests(data);
        } catch (error) {
            console.error('Error fetching requests:', error);
            requestsBody.innerHTML = '<tr><td colspan="10">Failed to load requests.</td></tr>';
        }
    }

    async function cancelRequest(requestId, button) {
        if (!confirm('Are you sure you want to cancel this request?')) {
            return;
        }
        button.disabled = true;
        try {
            const response = await fetch('../controllers/Cancel_Requests.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ request_id: requestId })
            });
            if (!response.ok) {
                throw new Error('Failed to cancel request');
            }
            const result = await response.json();
            if (result.success) {
                button.textContent = 'Cancelled';
                button.disabled = true;
                // Update the status cell in the same row
                const statusCell = button.closest('tr').querySelector('td[class^="status-"]');
                if (statusCell) {
                    statusCell.textContent = 'Cancelled';
                    statusCell.className = 'status-cancelled';
                }
            } else {
                alert('Failed to cancel request: ' + result.message);
                button.disabled = false;
            }
        } catch (error) {
            console.error('Error cancelling request:', error);
            alert('Error cancelling request');
            button.disabled = false;
        }
    }

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (isNaN(date)) return dateString;
        return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function populateRequests(requests) {
        if (!Array.isArray(requests) || requests.length === 0) {
            requestsBody.innerHTML = '<tr><td colspan="10">No requests found.</td></tr>';
            return;
        }

        requestsBody.innerHTML = '';
        requests.forEach(request => {
            const isCancelled = request.status && request.status.toLowerCase() === 'cancelled';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${request.product_name || ''}</td>
                <td>${request.pharmacy_name || ''}</td>
                <td>${request.quantity || ''}</td>
              
                <td>${formatDate(request.request_date)}</td>
                <td class="status-${request.status || ''}">${capitalizeFirstLetter(request.status || '')}</td>
                
          
             
               
                <td>
                    <button class="cancel-btn" data-id="${request.request_id}" ${isCancelled ? 'disabled' : ''}>Cancel</button>
                </td>
            `;
            requestsBody.appendChild(tr);
        });

        // Add event listeners for cancel buttons
        requestsBody.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const requestId = btn.getAttribute('data-id');
                cancelRequest(requestId, btn);
            });
        });
    }

    fetchRequests();
});
