@extends('layouts.seller')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    /* Layout and Containers */
    .chat-wrapper { display:flex; justify-content:center; padding:20px; background:#f4f7f6; min-height:85vh; }
    .chat-container { width:100%; max-width:1200px; height:80vh; background:#fff; border-radius:15px; display:flex; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,.15); }
    
    /* Sidebar styling */
    .chat-sidebar { width:320px; border-right:1px solid #e5e7eb; background:#fff; display:flex; flex-direction:column; }
    .chat-header { height:65px; padding:0 20px; display:flex; align-items:center; background:#6b2f2f; color:#fff; font-weight:700; justify-content: space-between; }
    .contact-list { flex:1; overflow-y:auto; }
    .contact-item { display:flex; gap:12px; padding:14px 18px; cursor:pointer; border-bottom:1px solid #f1f5f9; transition: 0.2s; position: relative; }
    .contact-item:hover { background:#fdf2f2; }
    .contact-item.active { background:#fff8f8; border-left:4px solid #6b2f2f; }
    .contact-avatar { width:45px; height:45px; border-radius:50%; object-fit:cover; border: 1px solid #ddd; }

    /* Chat Main Area */
    .chat-area { flex:1; display:flex; flex-direction:column; background: #fff; }
    .chat-top-bar { height:65px; padding:0 20px; display:flex; align-items:center; justify-content: space-between; border-bottom:1px solid #e5e7eb; }
    
    /* Message Bubbles */
    .messages-box { flex:1; padding:20px; overflow-y:auto; background:#f9fafb; display: flex; flex-direction: column; }
    .msg-row { display:flex; margin-bottom:12px; width: 100%; position: relative; }
    .msg-row.sent { justify-content:flex-end; }
    .msg-row.received { justify-content:flex-start; }

    .message { max-width:70%; padding:10px 14px; border-radius:15px; font-size:13.5px; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .sent .message { background:#6b2f2f; color:#fff; border-bottom-right-radius: 2px; }
    .received .message { background:#fff; color:#111827; border-bottom-left-radius: 2px; border: 1px solid #e5e7eb; }
    .message-time { font-size:10px; opacity:0.6; text-align:right; margin-top:5px; }
    
    /* Order Attachment Specific UI */
    .order-attachment { background: #fff; color: #333 !important; border-radius: 10px; padding: 12px; border-left: 5px solid #db8522; margin-top: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .order-attachment b { color: #6b2f2f; font-size: 14px; }
    .order-attachment p { margin: 2px 0; font-size: 12px; color: #444; }

    /* Input Field Styling */
    .chat-input-form { padding:15px 20px; border-top:1px solid #e5e7eb; }
    .input-wrapper { display:flex; gap:12px; align-items:center; background:#f1f5f9; border-radius:25px; padding:8px 18px; }
    .chat-input { flex:1; border:none; background:transparent; outline:none; resize:none; font-size: 14px; }
    .btn-send-chat { background:#22c55e; color:#fff; width:40px; height:40px; border-radius:50%; border:none; display: flex; align-items: center; justify-content: center; }
</style>

<div class="chat-wrapper">
    <div class="chat-container">
        <div class="chat-sidebar">
            <div class="chat-header"><span>Messages</span></div>
            <div class="contact-list" id="contactList">
    @if(isset($admin))
    <div class="contact-item active" id="contact-admin-{{ $admin->id }}" onclick="selectContact({{ $admin->id }}, 'admin')">
        <img src="{{ $admin->image ? asset('storage/'.$admin->image) : asset('images/default-user.png') }}" class="contact-avatar">
        <div>
            <strong>Admin Support</strong>
            <div style="font-size:11px;color:#6b7280">System Administrator</div>
        </div>
    </div>
    @endif

    @foreach($riders as $rider)
    <div class="contact-item" id="contact-delivery-{{ $rider->id }}" onclick="selectContact({{ $rider->id }}, 'delivery')">
        <img src="{{ $rider->image ? asset('storage/'.$rider->image) : asset('images/default-user.png') }}" class="contact-avatar">
        <div>
            <strong>{{ $rider->name }}</strong>
            <div style="font-size:11px;color:#6b7280">Delivery Rider</div>
        </div>
    </div>
    @endforeach
</div>
        </div>

        <div class="chat-area">
            <div class="chat-top-bar">
                <div class="d-flex align-items-center gap-3" style="cursor:pointer" onclick="viewProfile()">
                    <img id="chatAvatar" src="{{ asset('images/default-user.png') }}" class="contact-avatar">
                    <div>
                        <div id="chatTitle" style="font-weight:700">Select Contact</div>
                        <div id="chatSubtitle" style="font-size:11px; color:#6b7280">Click to view profile</div>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-danger border-0" onclick="clearChat()">
                    <i class="bi bi-trash"></i> Clear Chat
                </button>
            </div>

            <div class="messages-box" id="messagesBox"></div>

            <form id="chatForm" class="chat-input-form">
                @csrf
                <input type="hidden" id="receiverId">
                <input type="hidden" id="receiverType">
                
                <div class="input-wrapper">
                    <button type="button" class="btn text-secondary p-0" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-image" style="font-size:20px"></i>
                    </button>
                    <button type="button" class="btn text-secondary p-0" onclick="openOrderModal()">
                        <i class="bi bi-cart-check" style="font-size:20px"></i>
                    </button>
                    
                    <input type="file" id="fileInput" class="d-none" onchange="uploadMedia(this)">
                    <textarea id="messageInput" class="chat-input" placeholder="Type a message..." rows="1"></textarea>
                    <button type="submit" class="btn-send-chat"><i class="bi bi-send-fill"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content text-center p-4">
            <img id="pModalImg" src="" class="rounded-circle mx-auto mb-3" style="width:80px; height:80px; object-fit:cover">
            <h5 id="pModalName" class="mb-0"></h5>
            <p id="pModalRole" class="text-muted small"></p>
            <hr>
            <div class="text-start">
                <p class="small mb-1"><b>Email:</b> <span id="pModalEmail"></span></p>
                <p class="small"><b>Phone:</b> <span id="pModalPhone"></span></p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light"><h5>Select Order to Share</h5></div>
            <div class="modal-body" id="orderList" style="max-height: 400px; overflow-y: auto;"></div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentReceiverId = null;
let currentReceiverType = null;
let activeUser = {};
let replyToId = null; 

/**
 * 1. SELECT CONTACT
 * Fetches staff profile and initializes chat history.
 */
function selectContact(id, type) {
    currentReceiverId = id;
    currentReceiverType = type;
    document.getElementById('receiverId').value = id;
    document.getElementById('receiverType').value = type;

    document.querySelectorAll('.contact-item').forEach(e => e.classList.remove('active'));
    const target = document.getElementById(`contact-${type}-${id}`);
    if(target) target.classList.add('active');

    // Fetch Profile details from the staff table
    fetch(`/seller/chat/profile/${type}/${id}`)
        .then(r => r.json())
        .then(data => {
            activeUser = data;
            document.getElementById('chatTitle').innerText = data.name;
            // Updates top-bar image using fetched staff image
            document.getElementById('chatAvatar').src = data.image ? `/storage/${data.image}` : `/images/default-user.png`;
        });
    loadMessages();
}

/**
 * 2. LOAD MESSAGES
 * Renders bubbles, handles attachments, and adds action menus to both sides.
 */
function loadMessages() {
    if(!currentReceiverId) return;
    fetch(`/seller/chat/fetch/${currentReceiverId}/${currentReceiverType}`)
        .then(r => r.json())
        .then(data => {
            const messagesBox = document.getElementById('messagesBox');
            messagesBox.innerHTML = '';
            data.forEach(m => {
                const isSent = m.sender_type === 'seller';
                const row = document.createElement('div');
                row.className = 'msg-row ' + (isSent ? 'sent' : 'received');
                
                // Logic to prevent showing 'null' text when only an image is sent
                let messageText = (m.message && m.message !== 'null') ? m.message : '';
                let attachmentHtml = '';

                if(m.attachment) {
                    try {
                        const files = JSON.parse(m.attachment);
                        if(Array.isArray(files)){
                            files.forEach(path => {
                                attachmentHtml += `<img src="/storage/${path}" style="max-width:200px; border-radius:10px; display:block; margin-bottom:5px; cursor:pointer;" onclick="window.open('/storage/${path}')">`;
                            });
                        } else {
                            attachmentHtml = `<img src="/storage/${m.attachment}" style="max-width:200px; border-radius:10px; display:block; margin-bottom:5px;">`;
                        }
                    } catch(e) {
                        attachmentHtml = `<img src="/storage/${m.attachment}" style="max-width:200px; border-radius:10px; display:block; margin-bottom:5px;">`;
                    }
                }

                // Dropdown Menu for Reply and Delete (Available for both sides)
                const menuHtml = `
                    <div class="dropdown d-inline float-end ms-2">
                        <i class="bi bi-three-dots-vertical cursor-pointer" data-bs-toggle="dropdown" style="font-size:12px; opacity:0.5"></i>
                        <ul class="dropdown-menu shadow-sm border-0">
                            <li><a class="dropdown-item small" onclick="replyMessage(${m.id}, '${messageText.substring(0,20).replace(/'/g, "\\'") || 'Media'}')">Reply</a></li>
                            <li><a class="dropdown-item small text-danger" onclick="deleteMsg(${m.id}, 'me')">Delete for me</a></li>
                            ${isSent ? `<li><a class="dropdown-item small text-danger" onclick="deleteMsg(${m.id}, 'everyone')">Delete for everyone</a></li>` : ''}
                        </ul>
                    </div>`;

                row.innerHTML = `
                    <div class="message">
                        ${menuHtml} 
                        ${attachmentHtml}
                        ${messageText}
                        <div class="message-time">${new Date(m.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}</div>
                    </div>`;
                messagesBox.appendChild(row);
            });
            messagesBox.scrollTop = messagesBox.scrollHeight;
        });
}

/**
 * 3. UPLOAD MEDIA
 */
function uploadMedia(input) {
    if (!input.files || !input.files[0]) return;
    sendMessage(null, input.files[0]); 
    input.value = ''; 
}

/**
 * 4. ORDER MODAL LOGIC
 * Fetches orders using DB columns fname, lname, and address1.
 */
function openOrderModal() {
    fetch("{{ route('seller.chat.orders') }}")
        .then(r => r.json())
        .then(orders => {
            const orderList = document.getElementById('orderList');
            orderList.innerHTML = '';
            if (orders.length === 0) { orderList.innerHTML = '<div class="p-3 text-center text-muted">No orders found.</div>'; return; }
            
            orders.forEach(o => {
                const customer = `${o.fname || ''} ${o.lname || ''}`.trim();
                const address = (o.address1 || 'N/A').replace(/'/g, "\\'"); 
                orderList.innerHTML += `
                    <div class="p-3 border-bottom cursor-pointer hover-bg-light" onclick="shareOrder('${o.tracking_no}', '${customer}', '${address}', '${o.phone}')">
                        <b style="color:#6b2f2f;">Order #${o.tracking_no}</b><br><small class="text-muted">Customer: ${customer}</small>
                    </div>`;
            });
            new bootstrap.Modal(document.getElementById('orderModal')).show();
        });
}

function shareOrder(tracking, name, address, phone) {
    const orderHtml = `<div class="order-attachment"><b>📦 Shared Order Details</b><p><b>ID:</b> #${tracking}</p><p><b>Customer:</b> ${name}</p><p><b>Phone:</b> ${phone}</p><p><b>Address:</b> ${address}</p></div>`;
    sendMessage(orderHtml);
    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
    if (modalInstance) modalInstance.hide();
}

/**
 * 5. MESSAGE ACTIONS (DELETE/REPLY)
 * Handles soft delete (me) and hard delete (everyone).
 */
function deleteMsg(msgId, deleteType) {
    Swal.fire({
        title: 'Are you sure?',
        text: deleteType === 'everyone' ? "This deletes it for both people." : "This only removes it from your view.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6b2f2f'
    }).then(res => {
        if (res.isConfirmed) {
            fetch("{{ route('seller.chat.delete') }}", {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json' 
                },
                body: JSON.stringify({ id: msgId, type: deleteType }) 
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') loadMessages();
            });
        }
    });
}

function replyMessage(id, text) {
    replyToId = id; 
    const input = document.getElementById('messageInput');
    input.placeholder = `Replying to: "${text}..."`;
    input.focus();
}

/**
 * 6. VIEW PROFILE
 */
function viewProfile() {
    if(!activeUser.id) return;
    document.getElementById('pModalImg').src = document.getElementById('chatAvatar').src;
    document.getElementById('pModalName').innerText = activeUser.name;
    document.getElementById('pModalRole').innerText = activeUser.role || 'Member';
    document.getElementById('pModalEmail').innerText = activeUser.email || 'Not available';
    document.getElementById('pModalPhone').innerText = activeUser.phone || 'Not available';
    new bootstrap.Modal(document.getElementById('profileModal')).show();
}

/**
 * 7. CLEAR CHAT
 */
function clearChat() {
    Swal.fire({ title: 'Clear Chat?', text: "Delete your view of this chat?", icon: 'warning', showCancelButton: true, confirmButtonColor: '#6b2f2f' })
    .then(res => {
        if(res.isConfirmed) {
            fetch(`/seller/chat/clear/${currentReceiverId}/${currentReceiverType}`, { 
                method:'POST', 
                headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'} 
            })
            .then(() => loadMessages());
        }
    });
}

/**
 * 8. UNIFIED SEND LOGIC
 * Correctly sends text, images, and reply references.
 */
function sendMessage(msg, file = null) {
    if ((!msg && !file) || !currentReceiverId) return;

    let formData = new FormData();
    formData.append('receiver_id', currentReceiverId);
    formData.append('receiver_type', currentReceiverType);
    
    if (msg) formData.append('message', msg);
    if (file) formData.append('attachments[]', file); // Matches 'attachments.*' validation
    if (replyToId) formData.append('reply_to_id', replyToId); 

    fetch("{{ route('seller.chat.send') }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    }).then(r => r.json()).then(data => {
        const input = document.getElementById('messageInput');
        input.value = '';
        input.placeholder = "Type a message...";
        replyToId = null; 
        loadMessages();
    });
}

// UI Event Listeners
document.getElementById('messageInput').addEventListener('keypress', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage(this.value);
    }
});

document.getElementById('chatForm').addEventListener('submit', e => {
    e.preventDefault();
    const msgInput = document.getElementById('messageInput');
    if(msgInput.value.trim()) sendMessage(msgInput.value);
});

// Initialization
document.addEventListener('DOMContentLoaded', () => { 
    @if(isset($admin)) selectContact({{ $admin->id }}, 'admin'); @endif 
    setInterval(loadMessages, 5000); 
});
</script>

@endsection