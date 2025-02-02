const socket = new WebSocket("ws://localhost:8080");

socket.onopen = function() {
    console.log("Connected to chat server");
    
    // Send user ID to register as online
    const userId = sessionStorage.getItem("user_id");
    const role = sessionStorage.getItem("user_role"); // "client" or "pharmacy"
    
    if (userId && role) {
        socket.send(JSON.stringify({ action: "register", user_id: userId, role: role }));
    }

    // Fetch old messages from the server
    fetch(`fetchMessages.php?user_id=${userId}`)
        .then(response => response.json())
        .then(messages => {
            const chatBox = document.getElementById("messages");
            messages.forEach(msg => {
                const messageElement = document.createElement("p");
                messageElement.textContent = `${msg.sender_role}: ${msg.message}`;
                chatBox.appendChild(messageElement);
            });
        })
        .catch(error => console.error("Error fetching messages:", error));
};

// Listen for real-time messages
socket.onmessage = function(event) {
    const data = JSON.parse(event.data);

    if (data.action === "request_id") {
        // Server requests user ID after connection
        socket.send(JSON.stringify({ action: "register", user_id: userId, role: role }));
    } else {
        // Display received message
        const chatBox = document.getElementById("messages");
        const messageElement = document.createElement("p");
        messageElement.textContent = `${data.sender}: ${data.message}`;
        chatBox.appendChild(messageElement);
    }
};

// Send a message when the user submits the form
document.getElementById("chat-form").addEventListener("submit", function(event) {
    event.preventDefault();
    const messageInput = document.getElementById("message-input");
    const message = messageInput.value;

    if (message.trim() !== "") {
        const userId = sessionStorage.getItem("user_id");
        const receiverId = sessionStorage.getItem("receiver_id");
        const role = sessionStorage.getItem("user_role"); // "client" or "pharmacy"

        socket.send(JSON.stringify({
            action: "message",
            sender_id: userId,
            sender_role: role,
            receiver_id: receiverId,
            message: message
        }));

        messageInput.value = "";
    }
});
