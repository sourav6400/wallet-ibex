@extends('layouts.app')
@section('content')
    <div style="padding: 20px;">
        <section class="announcement-section">
            <div class="announcement-header">
                <h2>Announcements</h2>
                <button class="refresh-btn" onclick="location.reload();"><i class="fa-solid fa-rotate-right"></i> Refresh</button>
            </div>

            <div class="announcement-list">
                @if($announcements->count() > 0)
                    @foreach($announcements as $announcement)
                        <div class="announcement-card">
                            <div class="icon"><i class="fa-solid fa-bullhorn"></i></div>
                            <div class="details">
                                <h3>{{ $announcement->message }}</h3>
                                <p>{{ $announcement->message }}</p>
                                <span class="time">{{ $announcement->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 40px; color: #9ea7c6;">
                        <i class="fa-solid fa-inbox" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                        <p>No announcements at the moment.</p>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
