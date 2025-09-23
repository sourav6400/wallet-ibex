@extends('layouts.guest')
@section('content')
    <div class="onboarding_wrapper">
        <div class="onboarding_card three position-relative">
            {{-- <a href="#" class="skip_btn">Skip</a> --}}
            <div class="obCard_header">
                <img src="images/thumb/thumb4.png" alt="">
            </div>
            <div class="obCard_body">
                <h3>Redefine Ownership</h3>
                <p>IBEX Wallet is your gateway to total freedom and mastery over your digital wealth.</p>
                <button type="button" onclick="location.href='{{ route('wallet.selection') }}'">Continue</button>
                <ul class="ob_dots">
                    <li></li>
                    <li></li>
                    <li class="active"></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
