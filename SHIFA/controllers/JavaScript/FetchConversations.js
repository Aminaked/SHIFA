document.addEventListener('DOMContentLoaded', () => {
   

    fetchConversations();
});

async function fetchConversations() {
    try {
        const response = await fetch(`http://localhost/SHIFA/SHIFA/controllers/Conversation.php`);
        const conversations = await response.json();

        const container = document.getElementById('conversations-list');
        container.innerHTML = '';

        conversations.forEach(convo => {
            const convoElement = document.createElement('div');
            convoElement.className = 'conversation-card';
            convoElement.innerHTML = `
                <h3>${convo.name}</h3>
               
            `;
            const convoDetails = {
                recipient_id: convo.recipient_id,
                recipient_name: convo.name,
                recipient_type:convo.recipient_type
                
              };
            // Store recipient ID in sessionStorage when clicked
            convoElement.addEventListener('click', () => {
                sessionStorage.setItem('convoDetails', JSON.stringify(convoDetails));
                window.location.href = `../views/${currentUser.type}ChatPage.php`;
            });

            container.appendChild(convoElement);
        });

    } catch (error) {
        console.error('Error loading conversations:', error);
        alert('Failed to load conversations');
    }
}