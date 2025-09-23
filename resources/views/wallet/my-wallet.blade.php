@extends('layouts.app')
@section('content')
    <div class="dashboardRightMain_body p-0">
        <div class="myWallet_wrapper">
            @include('layouts.my-wallet-sidebar')

            @include('layouts.my-wallet-body')
            
        </div>
    </div>
@endsection