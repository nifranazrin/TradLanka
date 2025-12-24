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
                    <p>You will get your order within 2–4 working days.</p>
                </div>

                <div class="tracking-list">

                    <div class="tracking-item {{ $order->status >= 0 ? 'active' : '' }}">
                        <div class="tracking-icon"><i class="fas fa-file-invoice"></i></div>
                        <div>
                            <h6>Order Received</h6>
                            <p>{{ $order->created_at->format('d M, Y') }}</p>
                        </div>
                    </div>

                    <div class="tracking-item {{ $order->status >= 1 ? 'active' : '' }}">
                        <div class="tracking-icon"><i class="fas fa-box"></i></div>
                        <div>
                            <h6>Packed</h6>
                            <p>Seller is preparing your order</p>
                        </div>
                    </div>

                    <div class="tracking-item {{ $order->status >= 3 ? 'active' : '' }}">
                        <div class="tracking-icon"><i class="fas fa-warehouse"></i></div>
                        <div>
                            <h6>At Head Office</h6>
                            <p>Arrived at central hub</p>
                        </div>
                    </div>

                    <div class="tracking-item {{ $order->status >= 4 ? 'active' : '' }}">
                        <div class="tracking-icon"><i class="fas fa-truck"></i></div>
                        <div>
                            <h6>Out for Delivery</h6>
                            <p>Rider picked up package</p>
                        </div>
                    </div>

                    <div class="tracking-item {{ $order->status >= 4 ? 'active' : '' }}">
                        <div class="tracking-icon"><i class="fas fa-check"></i></div>
                        <div>
                            <h6>Delivered</h6>
                            @if($order->status >= 4)
                                <p class="done">Order Completed!</p>
                            @endif
                        </div>
                    </div>

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
