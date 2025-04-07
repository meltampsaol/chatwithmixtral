<template>
    <div class="chat-container">
      <div class="chat-box" v-for="(message, index) in messages" :key="index">
        <div :class="message.sender === 'user' ? 'user-message' : 'bot-message'">
          {{ message.text }}
        </div>
      </div>
  
      <div class="input-container">
        <input 
          v-model="userInput" 
          @keyup.enter="sendMessage"
          type="text" 
          placeholder="Type a message..." 
          class="chat-input"
        />
        <button @click="sendMessage" class="send-btn">Send</button>
      </div>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    data() {
      return {
        userInput: '',
        messages: [],
      };
    },
    methods: {
      async sendMessage() {
        if (!this.userInput.trim()) return;
  
        // Add user message to the chat
        this.messages.push({ sender: "user", text: this.userInput });
  
        // Make the API call to the Laravel backend
        try {
          const response = await axios.post('/api/chat', {
            prompt: this.userInput,
          });
  
          // Add bot's response to the chat
          this.messages.push({ sender: "bot", text: response.data.reply });
        } catch (error) {
          this.messages.push({ sender: "bot", text: "Sorry, something went wrong!" });
        }
  
        // Clear the input field
        this.userInput = '';
      },
    },
  };
  </script>
  
  <style scoped>
  .chat-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
  }
  
  .chat-box {
    margin-bottom: 10px;
    display: flex;
    flex-direction: column;
  }
  
  .user-message {
    background-color: #d1f7c4;
    padding: 10px;
    border-radius: 10px;
    align-self: flex-end;
    max-width: 80%;
    word-wrap: break-word;
  }
  
  .bot-message {
    background-color: #f0f0f0;
    padding: 10px;
    border-radius: 10px;
    align-self: flex-start;
    max-width: 80%;
    word-wrap: break-word;
  }
  
  .input-container {
    display: flex;
    margin-top: 10px;
  }
  
  .chat-input {
    flex: 1;
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ddd;
    margin-right: 10px;
  }
  
  .send-btn {
    padding: 8px 16px;
    border-radius: 5px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
  }
  
  .send-btn:hover {
    background-color: #0056b3;
  }
  </style>
  