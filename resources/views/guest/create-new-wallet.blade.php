@extends('layouts.guest')
@section('content')
    <div class="onboarding_wrapper createAnAccount position-relative">
        <a href="{{ route('wallet.selection') }}" class="caaBackBtn"><img src="{{ asset('images/icon/icon1.svg') }}"
                alt=""> Back</a>
        <div class="createAnAccount_card">
            <div class="createAnAccount_header createNewWallet">
                <img src="{{ asset('images/vector/vector5.png') }}" alt="">
            </div>
            <div class="createAnAccount_body">
                <h3>Your Wallet Name</h3>
                <p>Give your wallet a nice name</p>
                <form action="{{ route('wallet.pin') }}" method="POST">
                    @csrf
                    <div class="form_input">
                        <span>Wallet Name</span>
                        <input type="text" name="wallet_name" placeholder="Type a wallet name" required>
                        <div class="row m-0 g-2">
                            <div class="col-lg-6 ps-0">
                                <div class="form_btn">
                                    <button type="button" class="back"
                                        onclick="location.href='{{ route('wallet.selection') }}'">Back</button>
                                </div>
                            </div>
                            <div class="col-lg-6 pe-0">
                                <div class="form_btn">
                                    <button type="submit">Continue</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
