@extends('layouts.guest')
@section('content')
    <div class="onboarding_wrapper">
        <div class="onboarding_card one position-relative">
            <a href="{{ route('wallet.selection') }}" class="skip_btn">Skip</a>
            <div class="obCard_header">
                <img src="images/thumb/thumb2.png" alt="">
            </div>
            <div class="obCard_body">
                <h3>IBEX Wallet</h3>
                <p>Your all-in-one, non-custodial wallet - secure, intuitive, and built to manage every digital asset you
                    own</p>
                <button type="button" onclick="location.href = '{{ route('onboarding2') }}';">Continue</button>
                <ul class="ob_dots">
                    <li class="active"></li>
                    <li></li>
                    <li></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
