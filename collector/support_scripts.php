<style>
.support-card {
    transition: transform 0.2s ease-in-out;
    border: none;
}

.support-card:hover {
    transform: translateY(-5px);
}

.chat-message {
    margin-bottom: 1rem;
}

.support-message .bg-light {
    background-color: #f8f9fa !important;
}

.user-message .bg-primary {
    background-color: #174ea6 !important;
}
</style>

<script>
function viewTicket(ticketId) {
    // Implement ticket viewing functionality
    alert('Viewing ticket #' + ticketId + ' - This feature will be implemented soon.');
}

function sendChatMessage() {
    const messageInput = document.getElementById('chatMessage');
    const message = messageInput.value.trim();
    
    if (message) {
        const chatContainer = document.querySelector('.chat-container');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message user-message';
        messageDiv.innerHTML = `
            <div class="d-flex align-items-start mb-3 justify-content-end">
                <div class="flex-grow-1 me-3 text-end">
                    <div class="bg-primary text-white p-3 rounded">
                        <strong>You:</strong> ${message}
                    </div>
                    <small class="text-muted">Just now</small>
                </div>
                <div class="flex-shrink-0">
                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>
        `;
        chatContainer.appendChild(messageDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
        messageInput.value = '';
        
        // Simulate support response
        setTimeout(() => {
            const responseDiv = document.createElement('div');
            responseDiv.className = 'chat-message support-message';
            responseDiv.innerHTML = `
                <div class="d-flex align-items-start mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-headset"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="bg-light p-3 rounded">
                            <strong>Support Team:</strong> Thank you for your message. A support representative will respond shortly. In the meantime, you can check our FAQ for quick answers.
                        </div>
                        <small class="text-muted">Just now</small>
                    </div>
                </div>
            `;
            chatContainer.appendChild(responseDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }, 1000);
    }
}

// Handle Enter key in chat
if(document.getElementById('chatMessage')) {
    document.getElementById('chatMessage').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendChatMessage();
        }
    });
}
</script> 