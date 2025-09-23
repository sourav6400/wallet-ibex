@extends('layouts.app')
@section('content')
    <div class="dashboardRightMain_body p-0">
        <div class="settingsMain_wrapper">
            <div class="settingsMain_header">
                <ul>
                    <li><a href="{{ route('settings.backup_seed') }}">Backup Seed</a></li>
                    <li><a href="{{ route('settings.change_pin_view') }}">Change Pin</a></li>
                    <li><a href="{{ route('settings.faq') }}" class="active">FAQ</a></li>
                    <li><a href="{{ route('settings.terms_conditions') }}">Terms and Conditions</a></li>
                </ul>
            </div>
            <div class="settingsFaq_wrapper">
                <h4>Frequently Asked Questions</h4>
                <button type="button">View FAQ’s</button>
                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
            </div>
        </div>
    </div>
@endsection
