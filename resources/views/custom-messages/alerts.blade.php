@extends('layouts.app')
@section('content')
    <!-- DASHBOARD RIGHT SIDE HERE -->
    <div class="dashboardRight_main">
        <div class="dashboardRightMain_body">
            <section class="alert-section">
                <div class="alert-header">
                    <h2>Alerts</h2>
                    <button class="clear-btn" onclick="location.reload();"><i class="fa-solid fa-rotate-right"></i> Refresh</button>
                </div>

                <div class="alert-list">
                    @if($alerts->count() > 0)
                        @foreach($alerts as $alert)
                            <div class="alert-card warning">
                                <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                                <div class="details">
                                    <h3>{{ $alert->message }}</h3>
                                    <p>{{ $alert->message }}</p>
                                    <span class="time">{{ $alert->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div style="text-align: center; padding: 40px; color: #9ea7c6;">
                            <i class="fa-solid fa-inbox" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                            <p>No alerts at the moment.</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
