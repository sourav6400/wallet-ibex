@extends('layouts.app')
@section('content')
<div class="dashboardRightMain_body">
	<div class="support_body_wrapper">
		<div class="success-container">
			<div class="success-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="12" cy="12" r="10"></circle>
					<path d="M9 12l2 2 4-4"></path>
				</svg>
			</div>
			<h1 class="success-title">Form Submitted Successfully!</h1>
			<p class="success-message">
				Thank you for your submission. We have received your information and will get back to you shortly.
			</p>
			<div class="success-actions">
				<a href="{{ url('/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
			</div>
		</div>
	</div>
</div>

<style>
.support_body_wrapper {
	display: flex;
	justify-content: center;
	align-items: center;
	min-height: 500px;
	padding: 2rem;
}

.success-container {
	text-align: center;
	max-width: 500px;
	background: #fff;
	padding: 3rem 2rem;
	border-radius: 10px;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.success-icon {
	color: #10b981;
	margin-bottom: 1.5rem;
	display: inline-block;
	animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
	0% {
		transform: scale(0);
		opacity: 0;
	}
	50% {
		transform: scale(1.1);
	}
	100% {
		transform: scale(1);
		opacity: 1;
	}
}

.success-title {
	font-size: 1.75rem;
	color: #1f2937;
	margin-bottom: 1rem;
	font-weight: 600;
}

.success-message {
	color: #6b7280;
	font-size: 1rem;
	line-height: 1.6;
	margin-bottom: 2rem;
}

.success-actions {
	display: flex;
	gap: 1rem;
	justify-content: center;
	flex-wrap: wrap;
}

.btn {
	padding: 0.75rem 1.5rem;
	border-radius: 6px;
	text-decoration: none;
	font-weight: 500;
	transition: all 0.3s ease;
	display: inline-block;
}

.btn-primary {
	background-color: #3b82f6;
	color: white;
	border: 2px solid #3b82f6;
}

.btn-primary:hover {
	background-color: #2563eb;
	border-color: #2563eb;
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
	background-color: transparent;
	color: #6b7280;
	border: 2px solid #d1d5db;
}

.btn-secondary:hover {
	background-color: #f9fafb;
	border-color: #9ca3af;
	transform: translateY(-2px);
}
</style>
@endsection