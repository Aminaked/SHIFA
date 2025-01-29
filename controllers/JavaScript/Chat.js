const client_id = 1;
 
const pharmacy_id = 2; 


const userPfp = '../public/images/client.jpg'; 
const pharmacistPfp = '../public/images/pharmacy.jpg'; 



function fetchMessages() {
    fetch(`fetch_messages.php?user_id=${client_id}&other_user_id=${pharmacy_id}`)
        .then((response) => response.json())
        .then((messages) => {
            const messageContainer = document.getElementById('messages');
            messageContainer.innerHTML = '';

            messages.forEach((msg) => {

                const isUser = msg.sender_id == client_id; // Check if the sender is the logged-in user
                const pfp = isUser ? userPfp : pharmacistPfp; // Choose the correct PFP

                // Create message bubble
                const messageWrapper = document.createElement('div');
                messageWrapper.style.display = 'flex';
                messageWrapper.style.marginBottom = '10px';
                messageWrapper.style.justifyContent = isUser ? 'flex-end' : 'flex-start';

                const pfpImg = document.createElement('img');
                pfpImg.src = pfp;
                pfpImg.alt = isUser ? 'User' : 'Pharmacist';
                pfpImg.style.width = '40px';
                pfpImg.style.height = '40px';
                pfpImg.style.borderRadius = '50%';
                pfpImg.style.marginRight = isUser ? '0' : '10px';
                pfpImg.style.marginLeft = isUser ? '10px' : '0';

                const messageBubble = document.createElement('div');
                messageBubble.textContent = msg.message;
                messageBubble.style.maxWidth = '70%';
                messageBubble.style.padding = '10px';
                messageBubble.style.borderRadius = '10px';
                messageBubble.style.backgroundColor = isUser ? '#007bff' : '#f1f1f1';
                messageBubble.style.color = isUser ? 'white' : 'black';

                // Append PFP and bubble
                
                if (isUser) {
                    messageWrapper.appendChild(messageBubble);
                    messageWrapper.appendChild(pfpImg);
                } else {
                    messageWrapper.appendChild(pfpImg);
                    messageWrapper.appendChild(messageBubble);
                }

                messageContainer.appendChild(messageWrapper);
            });

            messageContainer.scrollTop = messageContainer.scrollHeight; // Scroll to the latest message
        });
}

// Send message
document.getElementById('chat-form').addEventListener('submit', (e) => {
    e.preventDefault();
    const messageInput = document.getElementById('message-input');
    const message = messageInput.value;

    fetch('send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sender_id: client_id, receiver_id: pharmacy_id, message }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.status === 'success') {
                messageInput.value = '';
                fetchMessages();
            }
        });
});

// Auto-fetch messages every 5 seconds
setInterval(fetchMessages, 5000);
fetchMessages();
