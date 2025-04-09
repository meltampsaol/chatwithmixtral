<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mistral Chatbot</title>

    <!-- Basic Styles -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
            padding: 20px;
        }

        h1 {
            color: #007bff;
        }

        .chat-box {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            height: 300px;
            overflow-y: scroll;
            width: 100%;
        }

        .message {
            padding: 10px;
            margin: 5px;
            border-radius: 5px;
            width: 80%;
            max-width: 80%;
        }

        .user-message {
            background-color: #d1f7c4;
            text-align: right;
            align-self: flex-end;
        }

        .bot-message {
            background-color: #f0f0f0;
            text-align: left;
            align-self: flex-start;
        }

        .input-container {
            margin-top: 10px;
            display: flex;
            justify-content: center;
        }

        .chat-input {
            width: 80%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-right: 10px;
        }

        .send-btn {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .send-btn:hover {
            background-color: #0056b3;
        }

        /* Add to your styles */
        .loading {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .loading-dot {
            animation: dotPulse 1.4s infinite;
        }

        @keyframes dotPulse {

            0%,
            80%,
            100% {
                opacity: 0.3;
            }

            40% {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Mistral Chatbot</h1>
        <div class="chat-box" id="chatBox">
            <!-- Chat messages will be dynamically inserted here -->
        </div>

        <div class="input-container">
            <input id="userInput" type="text" class="chat-input" placeholder="Type a message..." />
            <button id="sendButton" class="send-btn">Send</button>
        </div>
    </div>

    <script>
        const chatBox = document.getElementById('chatBox');
        const userInput = document.getElementById('userInput');
        const sendButton = document.getElementById('sendButton');
    
        function addMessage(sender, text) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('message');
            messageElement.classList.add(sender === 'user' ? 'user-message' : 'bot-message');
            messageElement.textContent = text;
            chatBox.appendChild(messageElement);
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    
        sendButton.addEventListener('click', async () => {
            const message = userInput.value.trim();
            if (message) {
                addMessage('user', message);
    
                try {
                    const response = await fetch('/api/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ prompt: message })
                    });
    
                    const data = await response.json();
                    const parsedReply = JSON.parse(data.reply);
                    const botReply = parsedReply.content || "No content available";
    
                    addMessage('bot', botReply);
                } catch (error) {
                    console.error(error);
                    addMessage('bot', 'Error: ' + error.message);
                }
    
                userInput.value = '';
            }
        });
    
        userInput.addEventListener('keyup', (event) => {
            if (event.key === 'Enter') {
                sendButton.click();
            }
        });
    </script>
    
</body>

</html>
