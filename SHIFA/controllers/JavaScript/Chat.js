const socket = new WebSocket('ws://localhost:8083');
const messagesContainer = document.getElementById('chat-messages');
const messageInput = document.getElementById('message-input');
const sendButton = document.getElementById('send-button');
const recipientStatus = document.getElementById('recipient-status');
let activeMenu = null;
let messageMap = new Map();

// Register user with WebSocket server
socket.onopen = function () {

    const regData = {
        type: 'register',
        id: currentUser.id.toString(),
        user_type: currentUser.type,
        name: currentUser.name || 'Unknown'
    };
    console.log('Registering user:', regData);
    socket.send(JSON.stringify(regData));
    loadPreviousMessages();
};

// Handle incoming messages
socket.onmessage = function (event) {
    try {
        const data = JSON.parse(event.data);
        console.log("Received WebSocket message", data);
        switch (data.type) {
            case 'message':
                const messageData = {
                    sender_id: data.from_id,
                    content: data.message,
                    timestamp: data.timestamp || Date.now(),
                    status: 'received'
                };
                console.log("Storing new message in messageMap:"),
                    // Store message metadata
                    messageMap.set(data.message_id, messageData);

                // 5. Verify storage
                console.log('Storage Verification:', {
                    storedId: data.message_id,
                    inMap: messageMap.has(data.message_id),
                    actualContents: messageMap.get(data.message_id)
                });

                addMessageToChat(
                    data.message,
                    data.from_type,
                    (data.from_id == currentUser.id) && (data.from_type == currentUser.type),
                    data.timestamp,
                    data.message_id  // Now properly passed
                );
                break;

            case 'edit':
                console.log("Processing edit for message:", data.message_id, "Current state:", messageMap.get(data.message_id));
                if (messageMap.has(data.message_id)) {
                    messageMap.get(data.message_id).content = data.new_content;
                    updateMessageUI(data.message_id, data.new_content);
                } else {
                    console.warn("Tried to edit non-existent message:", data.message_id);
                }
                break;

            case 'delete':
                console.log("Processing delete for message:", data.message_id, "Exists:", messageMap.has(data.message_id));
                if (messageMap.has(data.message_id)) {
                    messageMap.get(data.message_id).deleted = true;
                    hideMessageUI(data.message_id);
                } else {
                    console.warn("Tried to delete non-existent message:", data.message_id);
                }
                break;

            case 'status_update':
                updateRecipientStatus(data.online);
                break;
        }
    } catch (e) {
        console.error("Error processing message:", e);
    }

};


// UI Functions
function addMessageToChat(message, senderType, isSent, timestamp, messageId) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
    messageDiv.dataset.messageId = messageId;

    const menuHTML = isSent ? `
        <div class="message-menu">
            <button class="menu-trigger" onclick="toggleMenu(event, '${messageId}')">
               <img src="../public/images/ellipsis-vertical-solid.svg" >
            </button>
            <div class="menu-dropdown">
                <button onclick="handleEdit('${messageId}')">Edit</button>
                <button onclick="handleDelete('${messageId}')">Delete</button>
            </div>
        </div>
    ` : '';

    messageDiv.innerHTML = `
        <img src="../public/images/${senderType}.jpg" class="message-avatar">
        <div class="message-content">
            <div class="message-info">
                ${isSent ? currentUser.name : recipient.name}
                <span class="message-time">${formatTime(timestamp)}</span>
            </div>
            <div class="message-text">${message}</div>
        </div>
        ${menuHTML}
    `;

    messagesContainer.appendChild(messageDiv);
    scrollToBottom();
}
function toggleMenu(event, messageId) {
    event.stopPropagation();
    const menu = event.currentTarget.nextElementSibling;

    // Close other menus
    if (activeMenu && activeMenu !== menu) {
        activeMenu.classList.remove('active');
    }

    menu.classList.toggle('active');
    activeMenu = menu.classList.contains('active') ? menu : null;
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('.message-menu')) {
        if (activeMenu) {
            activeMenu.classList.remove('active');
            activeMenu = null;
        }
    }
});

function handleEdit(messageId) {
    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
    const currentContent = messageElement.querySelector('.message-text').innerText;

    const newContent = prompt('Edit your message:', currentContent);
    if (newContent && newContent !== currentContent) {
        // Send edit command via WebSocket
        const editData = {
            type: 'edit',
            message_id: messageId,
            user_id: currentUser.id.toString(),
            new_content: newContent,
            original_content: currentContent,
            // recipient_id: recipient.id.toString()
        };
        socket.send(JSON.stringify(editData));
    }
}

function handleDelete(messageId) {
    if (confirm('Are you sure you want to delete this message?')) {
        // Send delete command via WebSocket
        const deleteData = {
            type: 'delete',
            message_id: messageId,
            user_id: currentUser.id.toString(),
            // recipient_id: recipient.id.toString()
        };
        socket.send(JSON.stringify(deleteData));
    }
}
function updateMessageUI(messageId, newContent) {
    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
    if (messageElement) {
        messageElement.querySelector('.message-text').textContent = newContent;
        messageElement.querySelector('.message-time').innerHTML += ' (edited)';
    }
}
function hideMessageUI(messageId) {
    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
    if (messageElement) {
        messageElement.style.opacity = '0.5';
        messageElement.querySelector('.message-text').textContent = 'Message deleted';
        messageElement.querySelector('.message-menu').remove();
    }
}

function loadPreviousMessages() {

    
    fetch('http://localhost/SHIFA/SHIFA/controllers/FetchMessages.php', {
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
            console.log('--- RAW MESSAGES FROM SERVER ---');
            console.log(messages);
            messages.forEach(msg => {
                console.log("Storing historical message:");
                // Store message metadata
                messageMap.set(msg.chat_id, {
                    sender_id: msg.sender_id,
                    sender_type: msg.sender_type,
                    content: msg.message,
                    deleted: msg.is_deleted
                });
                console.log('--- AFTER LOADING MESSAGES ---');
               
                const isSent = (msg.sender_id == currentUser.id)&& (msg.sender_type == currentUser.type);
                addMessageToChat(msg.message, isSent ? currentUser.type : recipient.type, isSent,
                    new Date(msg.timestamp).getTime() / 1000, msg.chat_id);
                const mapEntries = Array.from(messageMap.entries()).map(([id, data]) => ({
                    message_id: id,
                    ...data
                }));
                console.table(mapEntries);
            });
        })
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
        const msgData = {
            type: 'message',
            to_id: recipient.id.toString(), // Ensure string ID
            to_type: recipient.type,
            message: message,
            from_id: currentUser.id.toString(), // Explicit sender
            from_type: currentUser.type
        };
        console.log('Sending message:', msgData);
        try {
            const jsonMsg = JSON.stringify(msgData);
            if (socket.readyState === WebSocket.OPEN) {
                socket.send(jsonMsg);
            } else {
                console.error('WebSocket not open, state:', socket.readyState);
            }
        } catch (e) {
            console.error('Message send failed:', e);
        }

        socket.onerror = function (error) {
            console.error('Message send error:', error);
        };

        messageInput.value = '';
    }
}

