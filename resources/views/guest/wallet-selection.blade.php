@extends('layouts.guest')
@section('content')
    <div class="onboarding_wrapper createAnAccount position-relative px-4">
        <div class="walletSelection_wrapper">
            <div class="walletSelection_logo">
                <a href="#"><img src="images/logo/logo.png" alt=""></a>
            </div>

            <div class="walletSelectionCard_wrapper">
                <div class="walletSelection_Card" onclick="location.href='{{ route('wallet.pin') }}'">
                    <img src="images/vector/vector3.png" alt="">
                    <div class="walletSelectionCard_content">
                        <h3>Create a New Wallet</h3>
                        <p>Secure your assets with a 12-word seed phrase.</p>
                    </div>
                </div>
                <div class="walletSelection_Card" onclick="location.href='{{ route('wallet.restore') }}'">
                    <img src="images/vector/vector4.png" alt="">
                    <div class="walletSelectionCard_content">
                        <h3>Restore Your Wallet</h3>
                        <p>Recover access using your 12-word seed phrase.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
