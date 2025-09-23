@extends('layouts.guest')
@section('content')
    <div class="onboarding_wrapper">
        <div class="onboarding_card two position-relative">
            <a href="{{ route('wallet.selection') }}" class="skip_btn">Skip</a>
            <div class="obCard_header">
                <img src="images/thumb/thumb3.png" alt="">
            </div>
            <div class="obCard_body">
                <h3>Built for Privacy, Secured for You</h3>
                <p>A wallet that safeguards your digital assets in a fully private environment.</p>
                <button type="button" onclick="location.href='{{ route('onboarding3') }}'">Continue</button>
                <ul class="ob_dots">
                    <li></li>
                    <li class="active"></li>
                    <li></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
