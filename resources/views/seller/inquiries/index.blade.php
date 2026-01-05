@extends('layouts.seller')

@section('content')

<style>
    :root{
        --brand:#5b2c2c;
        --orange1:#c86c1d;
        --orange2:#db8522;
    }

    body{ background:#f5f7f9; }

    /* ================= TABLE ================= */
    .table-header{
        background:var(--brand);
        color:#fff;
        font-size:12px;
        text-transform:uppercase;
    }
    .table-row:hover{ background:#fafafa; }

    .badge-pending{
        background:#ffedd5;color:#9a3412;
        padding:4px 12px;border-radius:20px;
        font-size:12px;font-weight:600;
    }
    .badge-replied{
        background:#dcfce7;color:#166534;
        padding:4px 12px;border-radius:20px;
        font-size:12px;font-weight:600;
    }

    .btn-reply{
        background:#2563eb;color:#fff;
        padding:6px 14px;border-radius:6px;
        font-weight:600;border:none;cursor:pointer;
    }
    .btn-view{
        background:#f3f4f6;color:#374151;
        padding:6px 14px;border-radius:6px;
        border:1px solid #d1d5db;
        font-weight:600;cursor:pointer;
    }

    /* ================= MODAL BASE ================= */
    .global-modal{
        position:fixed;
        inset:0;
        z-index:999999;
        display:none;
        align-items:center;
        justify-content:center;
    }

    .modal-card{
        background:#fff;
        border-radius:14px;
        box-shadow:0 30px 60px rgba(0,0,0,.35);
        overflow:hidden;
        transform:translateY(-10px);
    }

    /* ================= HEADERS ================= */
    .reply-header{
        background:linear-gradient(135deg,var(--orange1),var(--orange2));
        padding:18px;
        text-align:center;
        border-bottom:1px solid rgba(255,255,255,.25);
    }
    .reply-header h3{
        margin:0;
        font-size:20px;
        font-weight:700;
        color:#fff;
        letter-spacing:.3px;
    }

    /* ================= FIELDS ================= */
    .emboss-field{
        background:#fff;
        border:1px solid #c7c7c7;
        box-shadow: inset 0 1px 2px rgba(0,0,0,.15);
        padding:8px 10px;
        border-radius:6px;
        font-size:14px;
        white-space:pre-wrap;
    }

    .customer-msg{
        background:#f0f2f5;
        font-style:italic;
        color:#4b5563;
    }

    /* ================= SWEET ALERT ================= */
    .swal2-popup{
        background:linear-gradient(135deg,#5b2c2c,#7a3a3a)!important;
        color:#fff!important;
        border-radius:14px!important;
    }
    .swal2-title,.swal2-html-container{color:#fff!important;}
    .swal2-confirm{
        background:#fff!important;
        color:#5b2c2c!important;
        font-weight:700;
        border-radius:8px;
    }
    .page-title{
    font-size:32px;
    font-weight:800;
    color:#111827;         
    letter-spacing:-0.5px;
   }

   .page-subtitle{
    font-size:15px;
    font-weight:500;
    color:#4b5563;          
    margin-top:6px;
   }

</style>

<div class="container px-4 mx-auto mt-4 mb-4">


    <h2 class="page-title">Customer Inquiries</h2>
    <p class="page-subtitle">View and reply to customer messages</p>


    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded',()=>{
                Swal.fire({
                    icon:'success',
                    title:'Reply Sent',
                    text:'{{ session('success') }}',
                    confirmButtonText:'OK'
                });
            });
        </script>
    @endif

    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto w-full">

        <table class="w-full min-w-full table-auto">
            <thead class="table-header">
                <tr>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Customer</th>
                    <th class="px-5 py-3 text-left">Email</th>
                    <th class="px-5 py-3 text-left w-full">Message</th>
                    <th class="px-7 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inquiries as $inq)
                <tr class="table-row border-b">
                    <td class="px-5 py-4">
                        <span class="{{ $inq->status=='replied'?'badge-replied':'badge-pending' }}">
                            {{ ucfirst($inq->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-sm">
                        <div>{{ $inq->created_at->format('d M Y') }}</div>
                        <div class="text-xs text-gray-400">{{ $inq->created_at->format('h:i A') }}</div>
                    </td>
                    <td class="px-5 py-4">{{ $inq->first_name }} {{ $inq->last_name }}</td>
                    <td class="px-5 py-4 text-blue-600">{{ $inq->email }}</td>
                   <td class="px-5 py-4 max-w-xs truncate">
                        {{ Str::limit($inq->message,40) }}
                    </td>

                    <td class="px-5 py-4 text-center">
                        @if($inq->status=='pending')
                            <button class="btn-reply"
                                onclick="openReplyModal(event,'{{ $inq->id }}','{{ $inq->email }}')">Reply</button>
                        @else
                            <button class="btn-view"
                                onclick="openViewHistory('{{ $inq->first_name }}',{{ json_encode($inq->message) }},{{ json_encode($inq->reply_message) }})">
                                View
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 d-flex justify-content-center">
        {{ $inquiries->links() }}
    </div>
</div>

{{-- ================= REPLY MODAL ================= --}}
<div id="replyModal" class="global-modal">
    <div class="absolute inset-0 bg-black bg-opacity-60" onclick="closeReplyModal()"></div>
    <div class="modal-card w-full max-w-md">
        <div class="reply-header"><h3>Reply to Customer</h3></div>
        <form id="replyForm" method="POST" class="p-4">
            @csrf
            <label class="text-xs text-gray-500">From</label>
            <div class="emboss-field mb-3">infotradlanka@gmail.com</div>

            <label class="text-xs text-gray-500">To</label>
            <div id="modalCustomerEmail" class="emboss-field mb-4"></div>

            <label class="text-xs text-gray-500">Message</label>
            <textarea name="reply_message" rows="5" required class="w-full border rounded p-2 mb-4"></textarea>

            <div class="text-right">
                <button type="button" onclick="closeReplyModal()" class="px-4 py-2 border rounded mr-2">Cancel</button>
                <button type="submit" class="px-4 py-2 text-white rounded" style="background:#5b2c2c;">Send</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= VIEW HISTORY ================= --}}
<div id="viewReplyModal" class="global-modal">
    <div class="absolute inset-0 bg-black bg-opacity-60" onclick="closeViewReply()"></div>
    <div class="modal-card w-full max-w-lg">
        <div class="reply-header"><h3>Conversation History</h3></div>
        <div class="p-5">
            <label class="text-xs text-gray-500">Customer (<span id="viewCustomerName"></span>)</label>
            <div id="viewOriginalMsg" class="emboss-field customer-msg mb-4"></div>

            <label class="text-xs text-green-700 font-bold">You Replied</label>
            <div id="viewReplyMsg" class="emboss-field mb-4" style="border-left:3px solid #166534"></div>

            <div class="text-right">
                <button onclick="closeViewReply()" class="px-5 py-2 text-white rounded" style="background:#5b2c2c;">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openReplyModal(e,id,email){
    e.preventDefault();
    replyForm.action="/seller/inquiries/"+id+"/send-reply";
    modalCustomerEmail.innerText=email;
    replyModal.style.display="flex";
    document.body.style.overflow="hidden";
}
function closeReplyModal(){replyModal.style.display="none";document.body.style.overflow="auto";}
function openViewHistory(name,msg,reply){
    viewCustomerName.innerText=name;
    viewOriginalMsg.innerText=msg;
    viewReplyMsg.innerText=reply;
    viewReplyModal.style.display="flex";
    document.body.style.overflow="hidden";
}
function closeViewReply(){viewReplyModal.style.display="none";document.body.style.overflow="auto";}
</script>

@endsection
