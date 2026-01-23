@extends('layouts.delivery')

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
    .contact-item { display:flex; gap:12px; padding:14px 18px; cursor:pointer; border-bottom:1px solid #f1f5f9; transition: 0.2s; position: relative; align-items: center; }
    .contact-item:hover { background:#fdf2f2; }
    .contact-item.active { background:#fff8f8; border-left:4px solid #6b2f2f; }
    
    /* ✅ Robust Avatar Styling */
    .avatar-wrapper { width:45px; height:45px; border-radius:50%; object-fit:cover; border: 1px solid #ddd; background: #eee; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
    .contact-avatar { width: 100%; height: 100%; object-fit: cover; }

    /* ✅ WhatsApp Style Hiding Logic */
    .contact-item.new-contact { display: none !important; }
    .searching .contact-item.new-contact { display: flex !important; }

    /* Search Bar Styling */
    .search-box { padding: 10px 15px; border-bottom: 1px solid #eee; }
    .search-input { border-radius: 20px; font-size: 13px; background: #f8f9fa; border: 1px solid #e9ecef; padding: 5px 15px; width: 100%; outline: none; }

    /* Notification Badge Styling */
    .unread-badge { background: #ff0000; color: white; font-size: 10px; font-weight: bold; padding: 2px 6px; border-radius: 50%; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; position: absolute; right: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }

    /* Chat Area */
    .chat-area { flex:1; display:flex; flex-direction:column; background: #fff; }
    .chat-top-bar { height:65px; padding:0 20px; display:flex; align-items:center; justify-content: space-between; border-bottom:1px solid #e5e7eb; }
    .messages-box { flex:1; padding:20px; overflow-y:auto; background:#f9fafb; display: flex; flex-direction: column; }
    .msg-row { display:flex; margin-bottom:12px; width: 100%; position: relative; }
    .msg-row.sent { justify-content:flex-end; }
    .msg-row.received { justify-content:flex-start; }

    .message { max-width:70%; padding:10px 14px; border-radius:15px; font-size:13.5px; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .sent .message { background:#6b2f2f; color:#fff; border-bottom-right-radius: 2px; }
    .received .message { background:#fff; color:#111827; border-bottom-left-radius: 2px; border: 1px solid #e5e7eb; }
    .message-time { font-size:10px; opacity:0.6; text-align:right; margin-top:5px; }
    
    .order-attachment { background: #fff !important; color: #333 !important; border-radius: 10px; padding: 12px; border-left: 5px solid #db8522; margin-top: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: left; }
    .chat-input-form { padding:15px 20px; border-top:1px solid #e5e7eb; }
    .input-wrapper { display:flex; gap:12px; align-items:center; background:#f1f5f9; border-radius:25px; padding:8px 18px; }
    .chat-input { flex:1; border:none; background:transparent; outline:none; resize:none; font-size: 14px; }
    .btn-send-chat { background:#22c55e; color:#fff; width:40px; height:40px; border-radius:50%; border:none; display: flex; align-items: center; justify-content: center; }
</style>

<div class="chat-wrapper">
    <div class="chat-container">
        <div class="chat-sidebar" id="sidebarContainer">
            <div class="chat-header"><span>Rider Messages</span></div>
            
            <div class="search-box">
                <input type="text" id="contactSearch" class="search-input" placeholder="Search staff or colleagues..." onkeyup="filterContacts()">
            </div>

            <div class="contact-list" id="contactList">
                @if(isset($admin))
                <div class="contact-item {{ request('id') == $admin->id ? 'active' : '' }}" id="contact-admin-{{ $admin->id }}" onclick="selectContact({{ $admin->id }}, 'admin')">
                    <div class="avatar-wrapper">
                        <img src="{{ $admin->image ? asset('storage/'.$admin->image) : asset('images/default-user.png') }}" 
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Admin&background=6b2f2f&color=fff';" class="contact-avatar">
                    </div>
                    <div class="flex-grow-1">
                        <strong>Admin Support</strong>
                        <div style="font-size:11px;color:#6b7280">System Administrator</div>
                    </div>
                    @if(isset($admin->unread_count) && $admin->unread_count > 0)
                        <span class="unread-badge" id="badge-admin-{{ $admin->id }}">{{ $admin->unread_count }}</span>
                    @endif
                </div>
                @endif

                @foreach($activeSellers as $seller)
                <div class="contact-item" id="contact-seller-{{ $seller->id }}" onclick="selectContact({{ $seller->id }}, 'seller')">
                    <div class="avatar-wrapper">
                        <img src="{{ $seller->image ? asset('storage/'.$seller->image) : asset('images/default-user.png') }}" 
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($seller->name) }}&background=random';" class="contact-avatar">
                    </div>
                    <div class="flex-grow-1">
                        <strong>{{ $seller->name }}</strong>
                        <div style="font-size:11px;color:#6b7280">Seller</div>
                    </div>
                    @if(isset($seller->unread_count) && $seller->unread_count > 0)
                        <span class="unread-badge" id="badge-seller-{{ $seller->id }}">{{ $seller->unread_count }}</span>
                    @endif
                </div>
                @endforeach

                @if(isset($allOtherStaff))
                    @foreach($allOtherStaff as $staff)
                    <div class="contact-item new-contact" id="contact-{{ $staff->role }}-{{ $staff->id }}" onclick="selectContact({{ $staff->id }}, '{{ $staff->role }}')">
                        <div class="avatar-wrapper">
                            <img src="{{ $staff->image ? asset('storage/'.$staff->image) : asset('images/default-user.png') }}" 
                                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($staff->name) }}&background=eee&color=333';" class="contact-avatar">
                        </div>
                        <div class="flex-grow-1">
                            <strong>{{ $staff->name }}</strong>
                            <div style="font-size:11px;color:#6b7280">Start Chat ({{ ucfirst($staff->role) }})</div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="chat-area">
            <div class="chat-top-bar">
                <div class="d-flex align-items-center gap-3" style="cursor:pointer" onclick="viewProfile()">
                    <div class="avatar-wrapper">
                        <img id="chatAvatar" src="{{ asset('images/default-user.png') }}" class="contact-avatar">
                    </div>
                    <div>
                        <div id="chatTitle" style="font-weight:700">Select Contact</div>
                        <div id="chatSubtitle" style="font-size:11px; color:#6b2f2f">Click to view profile</div>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-danger border-0" onclick="clearChat()">
                    <i class="bi bi-trash"></i> Clear Chat
                </button>
            </div>

            <div class="messages-box" id="messagesBox"></div>

            <form id="chatForm" class="chat-input-form">
                @csrf
                <div class="input-wrapper">
                    <button type="button" class="btn text-secondary p-0" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-image" style="font-size:20px"></i>
                    </button>
                    <button type="button" class="btn text-secondary p-0" onclick="openOrderModal()">
                        <i class="bi bi-cart-check" style="font-size:20px"></i>
                    </button>
                    <input type="file" id="fileInput" class="d-none" multiple onchange="uploadMedia(this)">
                    <textarea id="messageInput" class="chat-input" placeholder="Type a message..." rows="1"></textarea>
                    <button type="submit" class="btn-send-chat"><i class="bi bi-send-fill"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="profileModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content text-center p-4"><img id="pModalImg" src="" class="rounded-circle mx-auto mb-3" style="width:80px;height:80px;object-fit:cover" onerror="this.src='https://ui-avatars.com/api/?name=User';"><h5 id="pModalName" class="mb-0"></h5><p id="pModalRole" class="text-muted small"></p><hr><div class="text-start"><p class="small mb-1"><b>Email:</b> <span id="pModalEmail"></span></p><p class="small"><b>Phone:</b> <span id="pModalPhone"></span></p></div></div></div></div>
<div class="modal fade" id="orderModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-light"><h5>Select Order to Share</h5></div><div class="modal-body" id="orderList" style="max-height: 400px; overflow-y: auto;"></div></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentReceiverId = null;
let currentReceiverType = null;
let activeUser = {};
let replyToId = null; 

/**
 * Corrected: Filter contacts and reveal inactive staff during search
 */
function filterContacts() {
    let input = document.getElementById('contactSearch').value.toLowerCase();
    let container = document.getElementById('sidebarContainer');
    let contacts = document.querySelectorAll('.contact-item');

    if (input.length > 0) {
        container.classList.add('searching');
    } else {
        container.classList.remove('searching');
    }

    contacts.forEach(item => {
        let name = item.querySelector('strong').innerText.toLowerCase();
        if (input.length > 0) {
            item.style.setProperty('display', name.includes(input) ? 'flex' : 'none', 'important');
        } else {
            item.style.removeProperty('display');
        }
    });
}

function selectContact(id, type) {
    currentReceiverId = id; currentReceiverType = type;
    document.querySelectorAll('.contact-item').forEach(e => e.classList.remove('active'));
    let target = document.getElementById(`contact-${type}-${id}`);
    if(target) target.classList.add('active');

    const badge = document.getElementById(`badge-${type}-${id}`);
    if(badge) badge.remove();

    fetch(`/delivery/chat/mark-read/${id}/${type}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });

    fetch(`/delivery/chat/profile/${type}/${id}`).then(r => r.json()).then(data => {
        activeUser = data;
        document.getElementById('chatTitle').innerText = data.name;
        const avatar = document.getElementById('chatAvatar');
        avatar.src = data.image ? `/storage/${data.image}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(data.name)}&background=random`;
    });
    loadMessages();
}

function loadMessages() {
    if(!currentReceiverId) return;
    fetch(`/delivery/chat/fetch/${currentReceiverId}/${currentReceiverType}`).then(r => r.json()).then(data => {
        const box = document.getElementById('messagesBox');
        box.innerHTML = '';
        data.forEach(m => {
            const isSent = m.sender_type === 'delivery';
            const row = document.createElement('div');
            row.className = 'msg-row ' + (isSent ? 'sent' : 'received');
            
            let messageText = (m.message && m.message !== 'null') ? m.message : '';
            let attachmentHtml = '';

            if(m.attachment) {
                try {
                    const files = JSON.parse(m.attachment);
                    if(Array.isArray(files)){
                        files.forEach(path => { attachmentHtml += `<img src="/storage/${path}" style="max-width:200px; border-radius:10px; display:block; margin-bottom:5px; cursor:pointer;" onclick="window.open('/storage/${path}')">`; });
                    } else { attachmentHtml = `<img src="/storage/${m.attachment}" style="max-width:200px; border-radius:10px; display:block; margin-bottom:5px;">`; }
                } catch(e) { attachmentHtml = `<img src="/storage/${m.attachment}" style="max-width:200px; border-radius:10px; display:block; margin-bottom:5px;">`; }
            }

            const menuHtml = `<div class="dropdown d-inline float-end ms-2"><i class="bi bi-three-dots-vertical cursor-pointer" data-bs-toggle="dropdown" style="font-size:12px; opacity:0.5"></i><ul class="dropdown-menu shadow-sm border-0"><li><a class="dropdown-item small" onclick="replyMessage(${m.id}, '${messageText.substring(0,20).replace(/'/g, "\\'")}')">Reply</a></li><li><a class="dropdown-item small text-danger" onclick="deleteMsg(${m.id}, 'me')">Delete for me</a></li>${isSent ? `<li><a class="dropdown-item small text-danger" onclick="deleteMsg(${m.id}, 'everyone')">Delete for everyone</a></li>` : ''}</ul></div>`;

            row.innerHTML = `<div class="message">${menuHtml}${attachmentHtml}${messageText}<div class="message-time">${new Date(m.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}</div></div>`;
            box.appendChild(row);
        });
        box.scrollTop = box.scrollHeight;
    });
}

function uploadMedia(input) { if (input.files && input.files[0]) { Array.from(input.files).forEach(f => sendMessage(null, f)); } input.value = ''; }

function openOrderModal() {
    fetch("{{ route('delivery.chat.orders') }}").then(r => r.json()).then(orders => {
        const orderList = document.getElementById('orderList');
        orderList.innerHTML = '';
        
        orders.forEach(o => {
            // FIX: Use o.fname only to avoid "null" showing in the UI
            const customerName = o.fname;
            
            // Fix: Ensure address exists before running .replace to avoid JS errors
            const safeAddress = o.address1 ? o.address1.replace(/'/g, "\\'") : 'No Address Provided';

            orderList.innerHTML += `
                <div class="p-3 border-bottom cursor-pointer hover-bg-light" 
                     onclick="shareOrder('${o.tracking_no}', '${customerName}', '${safeAddress}', '${o.phone}')">
                    <b>Order #${o.tracking_no}</b><br>
                    <small class="text-muted">Customer: ${customerName}</small>
                </div>`;
        });
        
        new bootstrap.Modal(document.getElementById('orderModal')).show();
    });
}

/**
 * Formats the selected order into a chat attachment and sends the message.
 */
function shareOrder(tracking, name, address, phone) {
    const orderHtml = `<div class="order-attachment">
        <b>📦 Shared Order Details</b>
        <p><b>ID:</b> #${tracking}</p>
        <p><b>Customer:</b> ${name}</p>
        <p><b>Phone:</b> ${phone}</p>
        <p><b>Address:</b> ${address}</p>
    </div>`;
    
    sendMessage(orderHtml); 
    bootstrap.Modal.getInstance(document.getElementById('orderModal')).hide();
}

function deleteMsg(msgId, deleteType) {
    Swal.fire({ title: 'Are you sure?', text: "Delete message?", icon: 'warning', showCancelButton: true, confirmColor: '#6b2f2f' }).then(res => {
        if (res.isConfirmed) {
            fetch("{{ route('delivery.chat.delete') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }, body: JSON.stringify({ id: msgId, type: deleteType }) }).then(() => loadMessages());
        }
    });
}

function replyMessage(id, text) { replyToId = id; const input = document.getElementById('messageInput'); input.placeholder = `Replying to: "${text}..."`; input.focus(); }

function viewProfile() { if(!activeUser.id) return; document.getElementById('pModalImg').src = document.getElementById('chatAvatar').src; document.getElementById('pModalName').innerText = activeUser.name; document.getElementById('pModalRole').innerText = activeUser.role; document.getElementById('pModalEmail').innerText = activeUser.email || 'Not available'; document.getElementById('pModalPhone').innerText = activeUser.phone || 'Not available'; new bootstrap.Modal(document.getElementById('profileModal')).show(); }

function clearChat() {
    Swal.fire({ title: 'Clear Chat?', text: "Delete your view of this chat?", icon: 'warning', showCancelButton: true, confirmColor: '#6b2f2f' })
    .then(res => { if(res.isConfirmed) { fetch(`/delivery/chat/clear/${currentReceiverId}/${currentReceiverType}`, { method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'} }).then(() => loadMessages()); } });
}

function sendMessage(msg, file = null) {
    if ((!msg && !file) || !currentReceiverId) return;
    let formData = new FormData();
    formData.append('receiver_id', currentReceiverId); formData.append('receiver_type', currentReceiverType);
    if (msg) formData.append('message', msg);
    if (file) formData.append('attachments[]', file);
    if (replyToId) formData.append('reply_to_id', replyToId); 

    fetch("{{ route('delivery.chat.send') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: formData }).then(() => {
        const input = document.getElementById('messageInput'); input.value = ''; input.placeholder = "Type a message..."; replyToId = null; loadMessages();
    });
}

document.getElementById('messageInput').addEventListener('keypress', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        const val = this.value; if(val.trim()) sendMessage(val);
    }
});

document.getElementById('chatForm').addEventListener('submit', e => { e.preventDefault(); const val = document.getElementById('messageInput').value; if(val.trim()) sendMessage(val); });

setInterval(loadMessages, 5000);

// Default selection logic
@if(isset($admin)) selectContact({{ $admin->id }}, 'admin'); @endif
</script>
@endsection