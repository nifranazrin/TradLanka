@extends('layouts.delivery') 

@section('content')

<style>
/* ================= POLISHED LAYOUT ================= */
.chat-wrapper{ display:flex; justify-content:center; padding:20px; background: #f4f7f6; min-height: 80vh; }
.chat-container{ 
    width:100%; max-width:1100px; height:80vh; background:#fff; 
    border:none; border-radius:15px; display:flex; overflow:hidden; 
    box-shadow: 0 10px 25px rgba(0,0,0,0.15); 
}

/* Sidebar */
.chat-sidebar{ width:280px; border-right:1px solid #edf2f7; background:#fff; display:flex; flex-direction:column; }
.chat-header{ 
    height:60px; padding:0 20px; display:flex; align-items:center; 
    background:#6b2f2f; color:#fff; font-size:15px; font-weight:700; 
}
.contact-list{ flex:1; overflow-y:auto; }
.contact-item{ display:flex; gap:12px; padding:15px 20px; border-bottom:1px solid #f8fafc; cursor:pointer; transition: 0.2s; }
.contact-item:hover { background: #fdf2f2; }
.contact-item.active { background:#fff8f8; border-left:4px solid #6b2f2f; }
.contact-avatar{ width:40px; height:40px; border-radius:50%; object-fit:cover; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

/* Chat Area */
.chat-area{ flex:1; display:flex; flex-direction:column; position:relative; background:#fff; }
.chat-top-bar{ height:60px; padding:0 20px; display:flex; align-items:center; border-bottom:1px solid #edf2f7; background:#fff; }

/* Messages */
.messages-box{ flex:1; padding:20px; overflow-y:auto; display:flex; flex-direction:column; gap:8px; background:#fdfdfd; }
.msg-row{ display:flex; width:100%; margin-bottom: 2px; }
.msg-row.sent{ justify-content:flex-end; }
.msg-row.received{ justify-content:flex-start; }

.message{
    display:inline-flex; flex-direction:column; max-width:65%; padding:10px 14px;
    border-radius:12px; font-size:13px; line-height:1.5; word-break:break-word;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.sent .message{ background:#6b2f2f; color:#fff; border-bottom-right-radius:2px; }
.received .message{ background:#e9ecef; color:#2d3436; border-bottom-left-radius:2px; }

/* Image Previews & UI Fixes */
.image-preview-container {
    display: none; position: absolute; bottom: 80px; left: 20px; right: 20px; 
    background: white; padding: 15px; border: 1px solid #e2e8f0; 
    border-radius: 12px; box-shadow: 0 -5px 15px rgba(0,0,0,0.1); z-index: 100;
}
.preview-grid { display: flex; gap: 15px; flex-wrap: wrap; }
.preview-item { position: relative; border: 2px solid #6b2f2f; padding: 2px; border-radius: 6px; }
.preview-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
.remove-preview { 
    position: absolute; top: -12px; right: -12px; background: red; color: white; border-radius: 50%; 
    width: 24px; height: 24px; font-size: 14px; display: flex; align-items: center; justify-content: center; 
    cursor: pointer; border: 2px solid white; font-weight: bold;
}

/* Input Area */
.chat-input-form { padding: 15px 20px; background: #fff; border-top: 1px solid #edf2f7; }
.input-wrapper { display: flex; align-items: center; gap: 12px; background: #f8f9fa; padding: 5px 15px; border-radius: 30px; border: 1px solid #e2e8f0; }
.chat-input{ flex: 1; border: none; background: transparent; padding: 10px 5px; font-size: 14px; outline: none; resize: none; }

.btn-send-chat {
    background: #28a745 !important; color: white !important;
    width: 42px; height: 42px; border-radius: 50%; border: none;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3); flex-shrink: 0;
}
</style>

<div class="chat-wrapper">
    <div class="chat-container">
        <div class="chat-sidebar">
            <div class="chat-header">Contacts</div>
            <div class="contact-list">
                @if($admin)
                <div class="contact-item active" id="contact-admin-{{ $admin->id }}" onclick="selectContact({{ $admin->id }}, 'admin', 'Admin Support')">
                    <img src="{{ asset('images/default-user.png') }}" class="contact-avatar">
                    <div>
                        <h6 style="margin:0; font-size:14px; font-weight:700;">Admin Support</h6>
                        <p style="margin:0; font-size:11px; color:#636e72;">Administrator</p>
                    </div>
                </div>
                @endif

                @foreach($sellers as $seller)
                <div class="contact-item" id="contact-seller-{{ $seller->id }}" onclick="selectContact({{ $seller->id }}, 'seller', '{{ $seller->name }}')">
                    <img src="{{ asset('images/default-user.png') }}" class="contact-avatar">
                    <div>
                        <h6 style="margin:0; font-size:14px; font-weight:700;">{{ $seller->name }}</h6>
                        <p style="margin:0; font-size:11px; color:#636e72;">Seller</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="chat-area">
            <div class="chat-top-bar"><h6 id="chatTitle" style="margin:0; font-weight:700;">Admin Support</h6></div>
            <div class="messages-box" id="messagesBox"></div>

            <div class="image-preview-container" id="imagePreviewBox">
                <div class="preview-grid" id="previewGrid"></div>
            </div>

            <form id="chatForm" class="chat-input-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="receiver_id" id="receiverId" value="{{ $admin ? $admin->id : '' }}">
                <input type="hidden" name="receiver_type" id="receiverType" value="admin">
                
                <div class="input-wrapper">
                    <button type="button" class="btn border-0" onclick="document.getElementById('fileInput').click()"><i class="bi bi-image"></i></button>
                    <input type="file" id="fileInput" name="attachments[]" multiple hidden onchange="previewFiles(this)">
                    
                    <textarea id="messageInput" name="message" class="chat-input" placeholder="Type a message..." rows="1"></textarea>
                    
                    <button type="submit" class="btn-send-chat"><i class="bi bi-send-fill"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentReceiverId = {{ $admin ? $admin->id : 'null' }};
let currentReceiverType = 'admin';
let allSelectedFiles = [];

const messagesBox = document.getElementById('messagesBox');

function selectContact(id, type, name) {
    currentReceiverId = id;
    currentReceiverType = type;
    document.getElementById('chatTitle').innerText = name;
    document.getElementById('receiverId').value = id;
    document.getElementById('receiverType').value = type;

    document.querySelectorAll('.contact-item').forEach(el => el.classList.remove('active'));
    document.getElementById(`contact-${type}-${id}`).classList.add('active');

    loadMessages();
}

function previewFiles(input) {
    if (input.files && input.files.length > 0) {
        allSelectedFiles = [...allSelectedFiles, ...Array.from(input.files)];
        updateInputFiles();
        renderPreviews();
    }
}

function renderPreviews() {
    const grid = document.getElementById('previewGrid');
    const box = document.getElementById('imagePreviewBox');
    grid.innerHTML = ''; 
    if (allSelectedFiles.length > 0) {
        box.style.display = 'block';
        allSelectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const item = document.createElement('div');
                item.className = 'preview-item';
                item.innerHTML = `<span class="remove-preview" onclick="removeSingleFile(${index})">&times;</span><img src="${e.target.result}">`;
                grid.appendChild(item);
            }
            reader.readAsDataURL(file);
        });
    } else { box.style.display = 'none'; }
}

function removeSingleFile(index) {
    allSelectedFiles.splice(index, 1);
    updateInputFiles();
    renderPreviews();
}

function updateInputFiles() {
    const dt = new DataTransfer();
    allSelectedFiles.forEach(file => dt.items.add(file));
    document.getElementById('fileInput').files = dt.files;
}

function loadMessages() {
    if(!currentReceiverId) return;
    fetch(`{{ url('delivery/chat/fetch') }}/${currentReceiverId}/${currentReceiverType}`)
        .then(res => res.json())
        .then(data => {
            messagesBox.innerHTML = '';
            data.forEach(msg => {
                // ✅ Matches 'delivery' type from Seller Controller
                const isSent = msg.sender_type === 'delivery';
                const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                
                let imgHtml = '';
                if(msg.attachment) {
                    try {
                        const paths = JSON.parse(msg.attachment);
                        if(Array.isArray(paths)) {
                            paths.forEach(p => { imgHtml += `<img src="/storage/${p}" onclick="window.open('/storage/${p}', '_blank')" style="max-width:180px; display:block; margin-bottom:5px; border-radius:5px;">`; });
                        }
                    } catch(e) {}
                }

                messagesBox.innerHTML += `
                    <div class="msg-row ${isSent ? 'sent' : 'received'}">
                        <div class="message">
                            ${imgHtml}
                            ${msg.message || ''}
                            <span style="font-size:9px; display:block; text-align:right; opacity:0.7; margin-top:5px;">${time}</span>
                        </div>
                    </div>`;
            });
            messagesBox.scrollTop = messagesBox.scrollHeight;
        });
}

document.getElementById('chatForm').onsubmit = function(e) {
    e.preventDefault();
    if(!document.getElementById('messageInput').value.trim() && !allSelectedFiles.length) return;

    fetch("{{ route('delivery.chat.send') }}", {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(() => {
        document.getElementById('messageInput').value = '';
        allSelectedFiles = [];
        document.getElementById('imagePreviewBox').style.display = 'none';
        loadMessages();
    });
};

loadMessages();
setInterval(loadMessages, 5000);
</script>
@endsection