// WebSocket connection
const socket = new WebSocket('ws://localhost:8083');  
const messagesContainer = document.getElementById('chat-messages');
const messageInput = document.getElementById('message-input');
const sendButton = document.getElementById('send-button');
const recipientStatus = document.getElementById('recipient-status');

// Register user with WebSocket server
socket.onopen = function() {
    socket.send(JSON.stringify({
        type: 'register',
        id: currentUser.id,
        user_type: currentUser.type
    }));
    loadPreviousMessages();
    checkRecipientStatus();
};

// Handle incoming messages
socket.onmessage = function(event) {
    const data = JSON.parse(event.data);
    
    if (data.type === 'message') {
        const isSent = data.from_id == currentUser.id;
        addMessageToChat(data.message, data.from_type, isSent, data.timestamp);
    } else if (data.type === 'status_update') {
        updateRecipientStatus(data.online);
    }
};

// UI Functions
function addMessageToChat(message, senderType, isSent, timestamp) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
    
    if (!isSent) {
        messageDiv.innerHTML = `
            <img src="images/${senderType}_avatar.png" class="message-avatar">
            <div class="message-content">
                <div class="message-info">
                    ${isSent ? currentUser.name : recipient.name}
                    <span class="message-time">${formatTime(timestamp)}</span>
                </div>
                <div class="message-text">${message}</div>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-info">
                    ${isSent ? currentUser.name : recipient.name}
                    <span class="message-time">${formatTime(timestamp)}</span>
                </div>
                <div class="message-text">${message}</div>
            </div>
        `;
    }
    
    messagesContainer.appendChild(messageDiv);
    scrollToBottom();
}

function loadPreviousMessages() {
    fetch('FetchMessages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            user_id: currentUser.id,
            user_type: currentUser.type,
            recipient_id: recipient.id,
            recipient_type: recipient.type
        })
    })
    .then(response => response.json())
    .then(messages => {
        messages.forEach(msg => {
            const isSent = msg.sender_id == currentUser.id;
            addMessageToChat(msg.message, isSent ? currentUser.type : recipient.type, isSent, 
                           new Date(msg.timestamp).getTime() / 1000);
        });
    });
}

// Helper functions
function formatTime(timestamp) {
    return new Date(timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Event listeners
sendButton.addEventListener('click', sendMessage);
messageInput.addEventListener('keypress', (e) => e.key === 'Enter' && sendMessage());

function sendMessage() {
    const message = messageInput.value.trim();
    if (message) {
        socket.send(JSON.stringify({
            type: 'message',
            to_id: recipient.id,
            to_type: recipient.type,
            message: message
        }));
        messageInput.value = '';
    }
}