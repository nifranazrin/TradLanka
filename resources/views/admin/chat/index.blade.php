@extends('layouts.admin')

@section('content')

{{-- CHAT STYLES (Same as Seller side for consistency) --}}
<style>
    /* Layout */
    .chat-container { height: calc(100vh - 120px); background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: flex; }
    
    /* Sidebar */
    .chat-sidebar { width: 300px; border-right: 1px solid #e5e7eb; background: #f9fafb; display: flex; flex-direction: column; }
    .chat-header { padding: 20px; background: #212529; color: white; font-weight: bold; } /* Dark for Admin */
    .contact-list { flex: 1; overflow-y: auto; }
    .contact-item { padding: 15px 20px; border-bottom: 1px solid #eee; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; }
    .contact-item:hover, .contact-item.active { background: #e9ecef; }
    .contact-avatar { width: 40px; height: 40px; border-radius: 50%; background: #ccc; margin-right: 12px; object-fit: cover; }
    .contact-info h6 { margin: 0; font-size: 14px; font-weight: 600; color: #333; }
    .contact-info p { margin: 0; font-size: 12px; color: #777; }

    /* Chat Area */
    .chat-area { flex: 1; display: flex; flex-direction: column; background: #fff; }
    .chat-top-bar { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; align-items: center; background: #fff; }
    .messages-box { flex: 1; padding: 20px; overflow-y: auto; background: #fdfdfd; display: flex; flex-direction: column; gap: 15px; }
    
    /* Message Bubbles (Colors flipped for Admin context) */
    .message { max-width: 70%; padding: 10px 15px; border-radius: 12px; font-size: 14px; position: relative; word-wrap: break-word; }
    .message.sent { align-self: flex-end; background: #212529; color: white; border-bottom-right-radius: 2px; } /* Admin Sent = Dark */
    .message.received { align-self: flex-start; background: #e5e7eb; color: #333; border-bottom-left-radius: 2px; } /* Seller Sent = Gray */
    .message img { max-width: 100%; border-radius: 8px; margin-top: 5px; }
    .message-time { font-size: 10px; opacity: 0.7; margin-top: 4px; text-align: right; display: block; }

    /* Input */
    .chat-input-area { padding: 15px; border-top: 1px solid #eee; background: #fff; display: flex; align-items: center; gap: 10px; }
    .chat-input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 25px; outline: none; }
    .btn-icon { width: 40px; height: 40px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; }
    .btn-send { background: #212529; color: white; }
    
    /* Helpers */
    #fileInput { display: none; }
    .file-preview { display: none; position: absolute; bottom: 70px; left: 20px; background: white; padding: 5px; border: 1px solid #ddd; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius: 8px; }
    .file-preview img { width: 60px; height: 60px; object-fit: cover; }
</style>

<div class="container-fluid px-4 py-4" style="height: 100vh;">
    
    <div class="chat-container">
        
        {{-- 1. LEFT SIDEBAR: SELLER LIST --}}
        <div class="chat-sidebar">
            <div class="chat-header">
                <i class="bi bi-people-fill me-2"></i> Seller List
            </div>
            <div class="contact-list">
                @if($sellers->count() > 0)
                    @foreach($sellers as $seller)
                    <div class="contact-item" onclick="selectSeller({{ $seller->id }}, '{{ $seller->name }}', this)">
                        {{-- Avatar Logic --}}
                        @php 
                            $avatar = $seller->image ? asset('storage/'.$seller->image) : asset('images/default-user.png');
                        @endphp
                        <img src="{{ $avatar }}" class="contact-avatar">
                        
                        <div class="contact-info">
                            <h6>{{ $seller->name }}</h6>
                            <p class="text-truncate" style="max-width: 180px;">{{ $seller->email }}</p>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="p-4 text-center text-muted">No sellers found.</div>
                @endif
            </div>
        </div>

        {{-- 2. RIGHT CHAT AREA --}}
        <div class="chat-area">
            
            {{-- Top Bar --}}
            <div class="chat-top-bar">
                <h5 class="mb-0 fw-bold" id="chatTitle">Select a Seller to Chat</h5>
            </div>

            {{-- Messages Box --}}
            <div class="messages-box" id="messagesBox">
                <div class="text-center text-muted mt-5">
                    <i class="bi bi-chat-square-text fs-1"></i>
                    <p class="mt-2">Select a seller from the left sidebar to start chatting.</p>
                </div>
            </div>

            {{-- Image Preview --}}
            <div class="file-preview" id="filePreview">
                <img id="previewImg" src="">
                <div style="position:absolute; top:-5px; right:-5px; background:red; color:white; border-radius:50%; width:18px; height:18px; font-size:10px; text-align:center; cursor:pointer;" onclick="clearFile()">&times;</div>
            </div>

            {{-- Input Area (Hidden initially) --}}
            <form id="chatForm" class="chat-input-area" enctype="multipart/form-data" style="display:none;">
                @csrf
                <input type="hidden" name="receiver_id" id="receiverId">

                {{-- Attachment --}}
                <button type="button" class="btn-icon" style="background:#f0f0f0;" onclick="document.getElementById('fileInput').click()">
                    <i class="bi bi-paperclip"></i>
                </button>
                <input type="file" id="fileInput" name="attachment" accept="image/*" onchange="showPreview(this)">

                {{-- Text Input --}}
                <input type="text" name="message" id="messageInput" class="chat-input" placeholder="Type a reply..." autocomplete="off">

                {{-- Send Button --}}
                <button type="submit" class="btn-icon btn-send">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        </div>

    </div>
</div>

{{-- JAVASCRIPT LOGIC --}}
<script>
    let currentSellerId = null;
    const messagesBox = document.getElementById('messagesBox');
    const chatForm = document.getElementById('chatForm');

    // 1. SELECT SELLER FUNCTION
    function selectSeller(id, name, element) {
        currentSellerId = id;
        document.getElementById('receiverId').value = id;
        document.getElementById('chatTitle').innerHTML = `<i class="bi bi-shop me-2"></i> ${name}`;
        document.getElementById('chatForm').style.display = 'flex'; // Show input

        // Highlight Active
        document.querySelectorAll('.contact-item').forEach(el => el.classList.remove('active'));
        element.classList.add('active');

        loadMessages();
    }

    // 2. LOAD MESSAGES
    function loadMessages() {
        if(!currentSellerId) return;

        fetch(`{{ url('/admin/chat/fetch') }}/${currentSellerId}`)
            .then(res => res.json())
            .then(data => {
                messagesBox.innerHTML = '';
                
                if(data.length === 0) {
                    messagesBox.innerHTML = '<p class="text-center text-muted mt-5">No messages yet.</p>';
                    return;
                }

                data.forEach(msg => {
                    // Logic: If sender_type is 'admin', it's SENT by ME.
                    const isSent = msg.sender_type === 'admin';
                    const bubbleClass = isSent ? 'sent' : 'received';
                    const date = new Date(msg.created_at);
                    const timeString = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                    let attachmentHtml = '';
                    if(msg.attachment) {
                        attachmentHtml = `<a href="/storage/${msg.attachment}" target="_blank"><img src="/storage/${msg.attachment}"></a>`;
                    }

                    const html = `
                        <div class="message ${bubbleClass}">
                            ${msg.message ? `<p class="mb-1">${msg.message}</p>` : ''}
                            ${attachmentHtml}
                            <span class="message-time">${timeString}</span>
                        </div>
                    `;
                    messagesBox.innerHTML += html;
                });
                scrollToBottom();
            });
    }

    // 3. SEND MESSAGE
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        // Ensure receiver_id is set
        if(!document.getElementById('receiverId').value) return;

        fetch("{{ route('admin.chat.send') }}", {
            method: "POST",
            body: formData,
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                document.getElementById('messageInput').value = '';
                clearFile();
                loadMessages();
            }
        });
    });

    // 4. HELPERS
    function showPreview(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('filePreview').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    function clearFile() {
        document.getElementById('fileInput').value = '';
        document.getElementById('filePreview').style.display = 'none';
    }
    function scrollToBottom() {
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    // Auto Refresh
    setInterval(() => { if(currentSellerId) loadMessages(); }, 4000);

</script>

@endsection