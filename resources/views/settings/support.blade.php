@extends('layouts.app')
@section('content')
<div class="dashboardRightMain_body">
	<div class="support_body_wrapper">
		<h2>IBEX Wallet Support</h2>
		<p>Keep your wallet secure and up to date. <br> Download the latest version now:</p>
		<a href="#">IBEXwallet.io/downloads</a>
		<div class="support_btn" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
			<img src="{{ asset('images/vector/vector12.png') }}" alt="">
			<span>Contact Support</span>
		</div>
		<h6>Anonymous IBEX ID</h6>
		<h5>9cc46939a6c4f6...95933fe7ab3676</h5>
	</div>
</div>

<div class="modal newAccount fade supportForm_wrapper" id="staticBackdrop" data-bs-backdrop="static"
	data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="newAccount_popup_wrapper position-relative">
				<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><img
						src="images/icon/icon17.svg" alt=""></button>
				<div class="newAccountPopup_header">
					<h3>Contact Support</h3>
				</div>
				
				<form action="https://api.web3forms.com/submit" method="POST">
				    <input type="hidden" name="access_key" value="a4bda2e3-368c-40d7-a220-38532ceedc55">
					<!--<input type="hidden" name="access_key" value="bfa896a2-d661-407a-a53e-781616cbf52a">--> <!--this will send to "imsourav6400@gmail.com" for testing-->
				    <input type="hidden" name="subject" value="New Support Request from IBEX Wallet">
				    <input type="hidden" name="from_name" value="IBEX Wallet">
					<input type="hidden" name="user_id" value="{{ $user_id }}">
				    <!--<input type="hidden" name="email" value="support@IBEXwallet.com">-->
				    <!--<input type="hidden" name="redirect" value="https://web3forms.com/success">-->
					<input type="hidden" name="redirect" value="https://web.ibexwallet.io/success">
					<div class="row g-0 m-0">
						<div class="col-12">
							<div class="form_input">
								<input type="email" name="sender" placeholder="Your Email" required>
							</div>
							<div class="form_input">
								<select name="title" required>
									<option hidden>Select Subjects</option>
									<option value="Balance Issue">Balance Issue</option>
									<option value="Transaction issue (Deposit / Withdrawal)">Transaction Issue (Deposit / Withdrawal)</option>
									<option value="Exchange">Exchange </option>
									<option value="Buy Crypto">Buy Crypto</option>
									<option value="Staking">Staking </option>
									<option value="Fee Question">Fee Question</option>
									<option value="Backup & Recovery">Backup & Recovery </option>
									<option value="Report a Bug, Security Issue, or Scam">Report a Bug, Security Issue, or Scam</option>
									<option value="Memes / Smart Contracts">Memes / Smart Contracts</option>
									<option value="Other Issues">Other Issues</option>
								</select>
							</div>
							<div class="form_input">
								<textarea placeholder="Describe your issues or share your ideas" name="details" required></textarea>
							</div>
						</div>
						<div class="col-12">
							<div class="form_btn">
								<button type="submit" class="changeName">Send</button>
							</div>
						</div>
					</div>
				</form>
				
				<!--<form action="{{ route('send_support_mail') }}" method="POST">-->
				<!--    @csrf-->
				<!--	<div class="row g-0 m-0">-->
				<!--		<div class="col-12">-->
				<!--			<div class="form_input">-->
				<!--				<input type="email" name="email" placeholder="Your Email" required>-->
				<!--			</div>-->
				<!--			<div class="form_input">-->
				<!--				<select name="subject" required>-->
				<!--					<option hidden>Select Subjects</option>-->
				<!--					<option value="Balance Issue">Balance Issue</option>-->
				<!--					<option value="Transaction issue (Deposit / Withdrawal)">Transaction issue (Deposit / Withdrawal)</option>-->
				<!--					<option value="Exchange">Exchange </option>-->
				<!--					<option value="Buy Crypto">Buy Crypto</option>-->
				<!--					<option value="Staking">Staking </option>-->
				<!--					<option value="Fee Question">Fee Question</option>-->
				<!--					<option value="Backup & Recovery">Backup & Recovery </option>-->
				<!--					<option value="Report a Bug, Security Issue, or Scam">Report a Bug, Security Issue, or Scam</option>-->
				<!--					<option value="Memes / Smart Contracts">Memes / Smart Contracts</option>-->
				<!--					<option value="Other Issues">Other Issues</option>-->
				<!--				</select>-->
				<!--			</div>-->
				<!--			<div class="form_input">-->
				<!--				<textarea placeholder="Describe your issues or share your ideas" name="details" required></textarea>-->
				<!--			</div>-->
				<!--		</div>-->
				<!--		<div class="col-12">-->
				<!--			<div class="form_btn">-->
				<!--				<button type="submit" class="changeName">Send</button>-->
				<!--			</div>-->
				<!--		</div>-->
				<!--	</div>-->
				<!--</form>-->
			</div>
		</div>
	</div>
</div>
@endsection