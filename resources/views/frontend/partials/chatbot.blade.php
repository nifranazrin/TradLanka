
<style>
df-messenger {
    position: fixed;
    bottom: 110px;          
    right: 20px;
    z-index: 9999;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;

   
    --df-messenger-titlebar-background: #5b2c2c !important;
    --df-messenger-titlebar-font-color: #ffffff !important;
    --df-messenger-chat-background-color: #fffaf5;
    
    /* Message Bubbles */
    --df-messenger-bot-message: #f3e5e5;
    --df-messenger-bot-font-color: #2c0a0a;
    --df-messenger-user-message: #eadada;
    --df-messenger-user-font-color: #2c0a0a;

 
    --df-messenger-input-box-color: #ffffff;
    --df-messenger-input-font-color: #2c0a0a;
    --df-messenger-send-icon: #5b2c2c;
}

df-messenger::part(titlebar) {
    background: #5b2c2c !important;
    background-color: #5b2c2c !important;
    color: #ffffff !important;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    display: flex !important; 
    
}


#tradlankaChatbot {
    --df-messenger-button-titlebar-color: #5b2c2c !important;
}


df-messenger::part(titlebar-title) {
    color: #ffffff !important;
    font-weight: 700;
}


df-messenger::part(titlebar-close-button) {
    display: block !important;   
    visibility: visible !important; 
    color: #ffffff !important;    
    opacity: 1 !important;
    cursor: pointer !important;
    width: 24px !important;      
    height: 24px !important;
}


df-messenger::part(titlebar)::after {
    border-bottom: none !important;
}


df-messenger::part(bot-message) {
    background-color: #f3e5e5 !important;
    color: #2c0a0a !important;
}

df-messenger::part(user-message) {
    background-color: #eadada !important;
    color: #2c0a0a !important;
}

df-messenger::part(input) {
    color: #2c0a0a !important;
}


df-messenger::part(launcher),
df-messenger::part(launcher-button) {
    display: none !important;
}


#chatbotBtn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 10000;
}

</style>

{{-- ================= CUSTOM ROBOT BUTTON ================= --}}
<a href="#" id="chatbotBtn"
   class="bg-[#5b2c2c] text-white w-12 h-12 rounded-full shadow-lg
          flex items-center justify-center hover:scale-110
          transition-transform duration-300">
    <i class="fas fa-robot text-xl"></i>
</a>

{{-- ================= DIALOGFLOW SCRIPT ================= --}}
<script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>

{{-- ================= CHATBOT COMPONENT ================= --}}
<df-messenger
    id="tradlankaChatbot"
    intent="WELCOME"
    chat-title="TradLanka Assistant"
    agent-id="a0932c0e-d13d-448d-b167-e22321412c89"
    language-code="en">
</df-messenger>

{{-- ================= CHATBOT TOGGLE LOGIC ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatbotBtn = document.getElementById('chatbotBtn');
    const chatbot = document.getElementById('tradlankaChatbot');

    if (!chatbotBtn || !chatbot) return;

  
    chatbotBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (chatbot.hasAttribute('open')) {
            chatbot.removeAttribute('open');
        } else {
            chatbot.setAttribute('open', '');
        }
    });

   
    chatbot.addEventListener('df-messenger-closed', function() {
        chatbot.removeAttribute('open');
    });
});
</script>