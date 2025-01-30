const socket = new WebSocket('ws://localhost:8080'); // WebSocket server

socket.onopen = function() {
    console.log('Connected to WebSocket server');
};

const sendButton = document.getElementById('send-button');
const chatInput = document.getElementById('chat-input');
const chatMessages = document.getElementById('chat-messages');

sendButton.onclick = function() {
    const message = chatInput.value;
    if (message) {
        const msgData = {
            message: message,
            pharmacy_id: pharmacy_id, // from session or PHP
            client_id: client_id // from session or PHP
        };
        socket.send(JSON.stringify(msgData));
        chatInput.value = ''; // clear input
    }
};

socket.onmessage = function(event) {
    const data = JSON.parse(event.data);
    const messageElement = document.createElement('div');
    messageElement.classList.add('message');
    messageElement.innerHTML = `<strong>${data.sender}:</strong> ${data.message}`;
    chatMessages.appendChild(messageElement);
};

// Fetch previous messages
$(document).ready(function() {
    $.get('FetchMessages.php', function(response) {
        const messages = JSON.parse(response);
        messages.forEach(function(msg) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('message');
            messageElement.innerHTML = `<strong>${msg.sender_role}:</strong> ${msg.message}`;
            chatMessages.appendChild(messageElement);
        });
    });
});
