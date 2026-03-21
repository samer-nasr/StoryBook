<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 flex flex-col h-[80vh]">
        <h1 class="text-2xl font-bold mb-4 text-center">AI Chatbot</h1>

        <div id="chat-box" class="flex-grow border rounded-lg p-4 mb-4 overflow-y-auto bg-gray-50">
            <!-- Chat messages will appear here -->
        </div>

        <form id="chat-form" class="flex">
            <input type="text" id="user-input" placeholder="Type your message..." class="flex-grow border rounded-l-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-r-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Send</button>
        </form>
    </div>

    <script>
        const chatBox = document.getElementById('chat-box');
        const chatForm = document.getElementById('chat-form');
        const userInput = document.getElementById('user-input');
        let messages = [{ role: 'system', content: 'You are a helpful AI assistant.' }]; // Initialize with system message

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const userMessage = userInput.value.trim();
            if (userMessage === '') return;

            appendMessage('user', userMessage);
            messages.push({ role: 'user', content: userMessage });
            userInput.value = '';

            // Show typing indicator
            const typingIndicator = appendMessage('bot', '...', true);

            try {
                const response = await fetch('/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ messages: messages })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                typingIndicator.remove(); // Remove typing indicator
                appendMessage('bot', data.message);
                messages.push({ role: 'assistant', content: data.message });
            } catch (error) {
                console.error('Error:', error);
                typingIndicator.remove(); // Remove typing indicator
                appendMessage('bot', 'Error: Could not get a response from the AI.');
            }
            chatBox.scrollTop = chatBox.scrollHeight;
        });

        function appendMessage(sender, text, isTypingIndicator = false) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('mb-3', 'p-3', 'rounded-lg', 'max-w-[70%]');

            if (sender === 'user') {
                messageElement.classList.add('bg-blue-500', 'text-white', 'self-end', 'ml-auto');
            } else {
                messageElement.classList.add('bg-gray-200', 'text-gray-800', 'mr-auto');
            }
            messageElement.textContent = text;
            chatBox.appendChild(messageElement);
            chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll to the bottom

            return messageElement; // Return for typing indicator
        }
    </script>
</body>
</html>