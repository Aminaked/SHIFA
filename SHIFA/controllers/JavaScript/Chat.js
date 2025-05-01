const socket = new WebSocket('ws://localhost:8083');  
const messagesContainer = document.getElementById('chat-messages');
const messageInput = document.getElementById('message-input');
const sendButton = document.getElementById('send-button');
const recipientStatus = document.getElementById('recipient-status');
<<<<<<< HEAD
let activeMenu = null; 
=======
>>>>>>> 41d628dbda25678228bb69905458f3ca5ec69358

// Register user with WebSocket server
socket.onopen = function() {
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
socket.onmessage = function(event) {
    const data = JSON.parse(event.data);
    
<<<<<<< HEAD
    switch(data.type) {
        case 'message':
            const isSent = data.from_id == currentUser.id;
            addMessageToChat(
                data.message, 
                data.from_type, 
                isSent, 
                data.timestamp,
                data.message_id // Make sure your server sends this
            );
            break;
            
        case 'edit':
            updateMessageUI(data.message_id, data.new_content);
            break;
            
        case 'delete':
            hideMessageUI(data.message_id);
            break;
            
        case 'status_update':
            updateRecipientStatus(data.online);
            break;
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
            recipient_id: recipient.id.toString()
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
            recipient_id: recipient.id.toString()
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
=======
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
            <img src="../public/images/${senderType}.jpg" class="message-avatar">
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
         <img src="../public/images/${senderType}.jpg" class="message-avatar">
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
>>>>>>> 41d628dbda25678228bb69905458f3ca5ec69358

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
        
        socket.onerror = function(error) {
            console.error('Message send error:', error);
        };
        
        messageInput.value = '';
    }
}

