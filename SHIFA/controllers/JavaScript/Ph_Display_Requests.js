document.addEventListener('DOMContentLoaded', function() {
    const requestsBody = document.getElementById('requestsBody');

    async function fetchRequests() {
        try {
            const response = await fetch('../controllers/Ph_Get_Requests.php');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            populateRequests(data.requests);
        } catch (error) {
            console.error('Error fetching requests:', error);
            requestsBody.innerHTML = '<tr><td colspan="10">Failed to load requests.</td></tr>';
        }
    }

    async function updateRequestStatus(requestId, newStatus, buttonApprove, buttonCancel, buttonFulfill) {
        if (!confirm(`Are you sure you want to mark this request as ${newStatus}?`)) {
            return;
        }
        // Disable all buttons during update
        buttonApprove.disabled = true;
        buttonCancel.disabled = true;
        buttonFulfill.disabled = true;

        try {
            const response = await fetch('../controllers/Ph_Update_Requests.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ request_id: requestId, status: newStatus })
            });
            if (!response.ok) {
                throw new Error('Failed to update request status');
            }
            const result = await response.json();
            if (result.success) {
                // Update UI accordingly
                if (newStatus === 'approved') {
                    buttonApprove.style.display = 'none';
                    buttonCancel.style.display = 'none';
                    buttonFulfill.style.display = 'inline-block';
                    buttonFulfill.disabled = false;
                } else {
                    buttonApprove.style.display = 'none';
                    buttonCancel.style.display = 'none';
                    buttonFulfill.style.display = 'none';
                }

                const statusTd = document.getElementById(`status-${requestId}`);
                if (statusTd) {
                    statusTd.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    if (newStatus === 'cancelled') {
                        statusTd.className = 'status-cancelled';
                    } else if (newStatus === 'fulfilled') {
                        statusTd.className = 'status-fulfilled';
                    } else if (newStatus === 'approved') {
                        statusTd.className = 'status-approved';
                    }
                }
            } else {
                alert('Failed to update request status: ' + result.message);
                buttonApprove.disabled = false;
                buttonCancel.disabled = false;
                buttonFulfill.disabled = false;
            }
        } catch (error) {
            console.error('Error updating request status:', error);
            alert('Error updating request status');
            buttonApprove.disabled = false;
            buttonCancel.disabled = false;
            buttonFulfill.disabled = false;
        }
    }

    function populateRequests(requests) {
        if (!Array.isArray(requests) || requests.length === 0) {
            requestsBody.innerHTML = '<tr><td colspan="10">No requests found.</td></tr>';
            return;
        }

        requestsBody.innerHTML = '';
        requests.forEach(request => {
            const tr = document.createElement('tr');

            const clientNameTd = document.createElement('td');
            clientNameTd.textContent = request.client_name || '';
            tr.appendChild(clientNameTd);

            const phoneNumberTd = document.createElement('td');
            phoneNumberTd.textContent = request.phone_number || '';
            tr.appendChild(phoneNumberTd);

            const medicationNameTd = document.createElement('td');
            medicationNameTd.textContent = request.product_name || '';
            tr.appendChild(medicationNameTd);

            const quantityTd = document.createElement('td');
            quantityTd.textContent = request.quantity || '';
            tr.appendChild(quantityTd);

            const clientNotesTd = document.createElement('td');
            clientNotesTd.textContent = request.client_notes || '';
            tr.appendChild(clientNotesTd);

            const requestDateTd = document.createElement('td');
            requestDateTd.textContent = request.request_date || '';
            tr.appendChild(requestDateTd);

            const statusTd = document.createElement('td');
            statusTd.id = `status-${request.request_id}`;
            const status = request.status ? request.status.toLowerCase() : 'pending';
            statusTd.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            if (status === 'cancelled') {
                statusTd.className = 'status-cancelled';
            } else if (status === 'fulfilled') {
                statusTd.className = 'status-fulfilled';
            } else if (status === 'approved') {
                statusTd.className = 'status-approved';
            }
            tr.appendChild(statusTd);

            // Action buttons column
            const actionsTd = document.createElement('td');

            // Approve button
            const approveBtn = document.createElement('button');
            approveBtn.textContent = 'Approve';
            approveBtn.disabled = (status === 'cancelled' || status === 'fulfilled' || status === 'approved');
            approveBtn.addEventListener('click', () => {
                updateRequestStatus(request.request_id, 'approved', approveBtn, cancelBtn, fulfillBtn);
            });
            actionsTd.appendChild(approveBtn);

            // Cancel button
            const cancelBtn = document.createElement('button');
            cancelBtn.textContent = 'Cancel';
            cancelBtn.disabled = (status === 'cancelled' || status === 'fulfilled');
            cancelBtn.addEventListener('click', () => {
                updateRequestStatus(request.request_id, 'cancelled', approveBtn, cancelBtn, fulfillBtn);
            });
            actionsTd.appendChild(cancelBtn);

            // Fulfill button
            const fulfillBtn = document.createElement('button');
            fulfillBtn.textContent = 'Fulfill';
            fulfillBtn.disabled = (status !== 'approved');
            fulfillBtn.addEventListener('click', () => {
                updateRequestStatus(request.request_id, 'fulfilled', approveBtn, cancelBtn, fulfillBtn);
            });
            actionsTd.appendChild(fulfillBtn);

            tr.appendChild(actionsTd);

            requestsBody.appendChild(tr);
        });
    }

    fetchRequests();
});
