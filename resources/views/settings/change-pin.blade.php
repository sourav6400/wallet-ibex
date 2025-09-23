@extends('layouts.app')
@section('content')
    <div class="dashboardRightMain_body p-0">
        <div class="settingsMain_wrapper">
            <div class="settingsMain_header">
                <ul>
                    <li><a href="{{ route('settings.backup_seed') }}">Private Keys</a></li>
                    <li><a href="{{ route('settings.change_pin_view') }}" class="active">Security</a></li>
                    <!--<li><a href="{{ route('settings.faq') }}">FAQ</a></li>-->
                    <!--<li><a href="{{ route('settings.terms_conditions') }}">Terms and Conditions</a></li>-->
                </ul>
            </div>

            @if (session('success_msg'))
                <div class="alert alert-success">
                    {{ session('success_msg') }}
                </div>
            @endif

            @if (session('error_msg'))
                <div class="alert alert-danger">
                    {{ session('error_msg') }}
                </div>
            @endif

            <div class="settingsBackupSeed_body">
                <div class="setting_chnagePin_wrapper">
                    <h3>Change Security PIN</h3>
                    <form id="changePinForm" method="POST" action="{{ route('settings.store_new_pin') }}">
                        @csrf
                        <div class="form_input">
                            <span>Old PIN</span>
                            <input type="password" id="oldPin" name="oldPin" placeholder="Enter Old PIN" minlength="6"
                                maxlength="6" pattern="\d{6}" required>
                            <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                        </div>
                        <div class="form_input">
                            <span>New PIN</span>
                            <input type="password" id="newPin" name="newPin" placeholder="Enter New PIN" minlength="6"
                                maxlength="6" pattern="\d{6}" required>
                            <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                        </div>
                        <div class="form_input">
                            <span>Repeat New PIN</span>
                            <input type="password" id="repeatPin" placeholder="Repeat New PIN" minlength="6" maxlength="6"
                                pattern="\d{6}" required>
                            <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                        </div>
                        <div class="form_btn primaryHover">
                            <button type="submit">Change PIN</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("changePinForm").addEventListener("submit", function(event) {
            let newPin = document.getElementById("newPin").value;
            let repeatPin = document.getElementById("repeatPin").value;

            if (newPin !== repeatPin) {
                event.preventDefault(); // stop form submission
                alert("New PIN and Repeat PIN do not match!");
            }
        });
    </script>
@endsection
