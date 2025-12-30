@extends('layouts.frontend')

@section('content')
{{-- FULL SCREEN BACKGROUND --}}
<div class="track-page">

    {{-- CENTER AREA --}}
    <div class="track-center">

        {{-- SEARCH CARD --}}
        <div class="track-card">
            <h3 class="track-title">Track Your Shipment</h3>
            <p class="track-subtitle">
                Enter your tracking number to view delivery progress
            </p>

            <form action="{{ route('track.order') }}" method="GET">
                <div class="track-input">
                    <input type="text"
                           name="tracking_no"
                           required
                           value="{{ request('tracking_no') }}"
                           placeholder="Tracking No (e.g. TRAD-123)">
                    <button type="submit">Track</button>
                </div>
            </form>

            {{-- EMPTY STATE HINT --}}
            <div class="track-hint">
                <span></span>
                <small>Fast • Secure • Real-time updates</small>
                <span></span>
            </div>

            @if(session('status'))
                <p class="track-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('status') }}
                </p>
            @endif
        </div>

        {{-- TRACKING RESULT --}}
        @isset($order)
            <div class="track-card result-card">

                <div class="result-header">
                    <h5>
                        Status for:
                        <span>{{ $order->tracking_no }}</span>
                    </h5>
                    
                    {{-- Status-based header message --}}
                    @if($order->status == '6')
                        <p class="text-danger fw-bold">This order has been cancelled and refunded.</p>
                    @elseif($order->status >= 7)
                        <p class="text-warning fw-bold">Cancellation process is in progress.</p>
                    @else
                        <p>You will get your order within 2–4 working days.</p>
                    @endif
                </div>

                <div class="tracking-list">
                    
                    {{-- 🛑 CANCELLATION JOURNEY (Shows only if status is 6, 7, or 8) --}}
                    @if(in_array($order->status, ['6', '7', '8']))
                        
                        {{-- 1. Cancellation Requested --}}
                        <div class="tracking-item active">
                            <div class="tracking-icon"><i class="fas fa-undo"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Cancellation Requested</h6>
                                <p>Requested by customer. Waiting for seller to review.</p>
                            </div>
                        </div>

                        {{-- 2. Seller Approved (Status 8 or 6) --}}
                        <div class="tracking-item {{ ($order->status == '8' || $order->status == '6') ? 'active' : '' }}">
                            <div class="tracking-icon"><i class="fas fa-user-check"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Approved by Seller</h6>
                                @if($order->status == '8' || $order->status == '6')
                                    <p>Seller verified and approved. Sent to Head Office for refund.</p>
                                @else
                                    <p class="text-muted italic">Waiting for seller verification...</p>
                                @endif
                            </div>
                        </div>

                        {{-- 3. Final Refund (Status 6 Only) --}}
                        <div class="tracking-item {{ $order->status == '6' ? 'active' : '' }}">
                            <div class="tracking-icon"><i class="fas fa-hand-holding-usd"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Refunded & Closed</h6>
                                @if($order->status == '6')
                                    <p class="done">Funds returned and stock updated!</p>
                                @else
                                    <p class="text-muted italic">Waiting for Head Office finalization...</p>
                                @endif
                            </div>
                        </div>

                    @else
                        {{-- 🚚 NORMAL DELIVERY JOURNEY (Original Steps) --}}
                        
                        {{-- 1. Order Placed --}}
                        <div class="tracking-item active">
                            <div class="tracking-icon"><i class="fas fa-shopping-bag"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Order Placed</h6>
                                <p>Successfully placed on {{ $order->created_at->format('d M, Y') }}</p>
                            </div>
                        </div>

                        {{-- 2. Order Received (Status 1) --}}
                        <div class="tracking-item {{ $order->status >= 1 ? 'active' : '' }}">
                            <div class="tracking-icon"><i class="fas fa-file-invoice"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Order Received</h6>
                                <p>{{ $order->status >= 1 ? 'Seller accepted your order' : 'Waiting for seller to accept...' }}</p>
                            </div>
                        </div>

                        {{-- 3. Packed (Status 2) --}}
                        <div class="tracking-item {{ $order->status >= 2 ? 'active' : '' }}">
                            <div class="tracking-icon"><i class="fas fa-box"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Packed</h6>
                                <p>Seller is preparing your order</p>
                            </div>
                        </div>

                        {{-- 4. At Head Office (Status 3 or 4) --}}
                        <div class="tracking-item {{ ($order->status == 3 || $order->status == 4) ? 'active' : '' }}">
                            <div class="tracking-icon"><i class="fas fa-warehouse"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">At Head Office</h6>
                                <p>Arrived at central hub</p>
                            </div>
                        </div>

                        {{-- 5. Delivered (Status 5) --}}
                        <div class="tracking-item {{ $order->status >= 5 ? 'active' : '' }}">
                            <div class="tracking-icon"><i class="fas fa-check-circle"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Delivered</h6>
                                @if($order->status >= 5)
                                    <p class="done">Order Completed!</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endisset

    </div>
</div>
@endsection
{{-- STYLES --}}
<style>
.track-page {
    min-height: 100vh;
    background:
        linear-gradient(rgba(0,0,0,.25), rgba(0,0,0,.25)),
        url('/storage/images/background.jpg') center / cover no-repeat fixed;
    display: flex;
    flex-direction: column;
}


.tracking-item.active::before {
    background: #5b2c2c;
}
.track-center {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 22px;
    padding-bottom: 50px;
}
.track-card {
    width: 100%;
    max-width: 500px;
    background: rgba(255,255,255,.96);
    border-radius: 20px;
    padding: 26px;
    box-shadow: 0 20px 40px rgba(0,0,0,.18);
    transform: translateY(-15px);
}
.track-title {
    text-align: center;
    font-weight: 800;
    color: #5b2c2c;
}
.track-subtitle {
    text-align: center;
    font-size: 14px;
    color: #6b7280;
    margin: 6px 0 18px;
}
.track-input {
    display: flex;
    border-radius: 999px;
    overflow: hidden;
    border: 2px solid #5b2c2c;
}
.track-input input {
    flex: 1;
    padding: 11px 14px;
    border: none;
}
.track-input button {
    background: #5b2c2c;
    color: white;
    padding: 0 22px;
    border: none;
    font-weight: 700;
}
.track-hint {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 18px;
    color: #9ca3af;
    font-size: 12px;
}
.track-hint span {
    flex: 1;
    height: 1px;
    background: #e5e7eb;
}
.track-error {
    margin-top: 10px;
    text-align: center;
    color: #dc2626;
    font-size: 13px;
}
.tracking-list { margin-top: 20px; }
.tracking-item {
    display: flex;
    gap: 15px;
    margin-bottom: 26px;
    position: relative;
}
.tracking-item::before {
    content: '';
    position: absolute;
    left: 18px;
    top: 38px;
    width: 3px;
    height: 100%;
    background: #e5e7eb;
}
.tracking-item:last-child::before { display: none; }
.tracking-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
}
.tracking-item.active .tracking-icon {
    background: #5b2c2c;
    color: #fff;
}
.done {
    color: #16a34a;
    font-weight: 700;
}
</style>
