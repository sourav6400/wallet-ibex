@extends('layouts.guest')
@section('content')
    <div class="onboarding_wrapper walletRestoreV2 createAnAccount position-relative">
        <a class="caaBackBtn" onclick="location.href='{{ route('wallet.selection') }}'" role="button"><img
                src="images/icon/icon1.svg" alt=""> Back</a>
        <div class="createAnAccount_card">
            <div class="createAnAccount_header createNewWallet">
                <img src="images/logo/logo_main.svg" alt="">
            </div>
            <div class="createAnAccount_body">
                <h3>Restore Your Wallet</h3>
                <p>Enter Your 12-Word Recovery Phrase</p>
                <form action="{{ route('wallet.restorePost') }}" method="POST" class="walletRestoreV2_form">
                    @csrf
                    <div class="form_input position-relative">
                        <input type="text" name="wallet_phrase" placeholder="Your 12 words backup phrase" required>
                        <span class="paste_icon"><i class="fa-solid fa-paste"></i></span>
                    </div>
                    @error('not_found')
                        <div class="text-red text-sm" style="color: red;">{{ $message }}</div>
                    @enderror
                    <div class="row m-0 g-0">
                        <div class="col-12">
                            <div class="form_btn mt-5">
                                <button type="submit">Restore Wallet</button>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form_btn">
                                <button type="button" class="back"
                                    onclick="location.href='{{ route('wallet.selection') }}'">Back</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
