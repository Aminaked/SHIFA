const socket = new WebSocket("ws://localhost:8080");

socket.onopen = function() {
    console.log("Connected to chat server");

    // Send pharmacy ID to register as online
    const pharmacyId = sessionStorage.getItem("pharmacy_id");
    const role = "pharmacy"; // Hardcoded role for pharmacy

    if (pharmacyId && role) {
        socket.send(JSON.stringify({ action: "register", user_id: pharmacyId, role: role }));
    }

    // Fetch old messages from the server
    fetch(`fetchMessages.php?user_id=${pharmacyId}`)
        .then(response => response.json())
        .then(messages => {
            const chatBox = document.getElementById("messages");
            messages.forEach(msg => {
                const messageElement = document.createElement("p");
                messageElement.textContent = `${msg.sender_id}: ${msg.message} (${msg.timestamp})`;
                chatBox.appendChild(messageElement);
            });
        })
        .catch(error => console.error("Error fetching messages:", error));
};

// Listen for real-time messages
socket.onmessage = function(event) {
    const data = JSON.parse(event.data);

    if (data.action === "request_id") {
        // Server requests pharmacy ID after connection
        const pharmacyId = sessionStorage.getItem("pharmacy_id");
        const role = "pharmacy";
        socket.send(JSON.stringify({ action: "register", user_id: pharmacyId, role: role }));
    } else {
        // Display received message
        const chatBox = document.getElementById("messages");
        const messageElement = document.createElement("p");
        messageElement.textContent = `${data.sender}: ${data.message} (${data.timestamp})`;
        chatBox.appendChild(messageElement);
    }
};

// Send a message when the pharmacy submits the form
document.getElementById("chat-form").addEventListener("submit", function(event) {
    event.preventDefault();
    const messageInput = document.getElementById("message-input");
    const message = messageInput.value;

    if (message.trim() !== "") {
        const pharmacyId = sessionStorage.getItem("pharmacy_id");
        const clientId = sessionStorage.getItem("client_id"); // The client the pharmacy is chatting with
        const role = "pharmacy"; // Hardcoded role for pharmacy

        socket.send(JSON.stringify({
            action: "message",
            sender_id: pharmacyId,
            sender_role: role,
            receiver_id: clientId,
            message: message,
            pharmacy_id: pharmacyId,
            client_id: clientId
        }));

        messageInput.value = "";
    }
});