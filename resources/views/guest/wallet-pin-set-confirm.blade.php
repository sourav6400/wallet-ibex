@extends('layouts.guest')
@section('content')
    <div class="onboarding_wrapper createAnAccount position-relative">
        <div class="walletPinSet_card">
            <div class="walletPinSetCard_header">
                <h3>Confirm Your Wallet PIN</h3>
                <p>Re-enter your 6-digit code to secure your wallet.</p>
            </div>
            <form action="{{ route('word.seed_phrase') }}" method="POST">
                @csrf
                <div class="walletPinSetCard_body">
                    <div class="walletPinInput_wrapper">
                        <div class="pin-container" id="pinDots">
                            <div class="pin-dot"></div>
                            <div class="pin-dot"></div>
                            <div class="pin-dot"></div>
                            <div class="pin-dot"></div>
                            <div class="pin-dot"></div>
                            <div class="pin-dot"></div>
                        </div>
                        <input type="hidden" value="{{ $wallet_pin }}" name="wallet_pin">
                        <input type="text" id="pinInput" inputmode="numeric" pattern="[0-9]*" autocomplete="off"
                            name="wallet_pin_confirm">
                    </div>
                    <div class="walletPinKey_wrapper">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="walletPinBtn">
                                    <button type="button">1</button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="walletPinBtn">
                                    <button type="button">2</button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="walletPinBtn">
                                    <button type="button">3</button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="walletPinBtn">
                                    <button type="button">4</button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="walletPinBtn">
                                    <button type="button">5</button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="walletPinBtn">
                                    <button type="button">6</button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="walletPinBtn">
                                    <button type="button">7</button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="walletPinBtn">
                                    <button type="button">8</button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="walletPinBtn">
                                    <button type="button">9</button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="walletPinBtn iconXmark">
                                    <button type="button"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="walletPinBtn">
                                    <button type="button">0</button>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="walletPinBtn icon disable">
                                    <button type="submit" disabled><i class="fa-solid fa-check"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="walletPinBackconti_wrapper">
                        <div class="row g-2 mt-1">
                            <div class="col-6">
                                <div class="walletPinBackconti_btn">
                                    <button type="button"
                                        onclick="location.href='{{ route('wallet.selection') }}'">Back</button>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="walletPinBackconti_btn">
                                    <button type="submit" class="continueBtn disable" disabled>Continue</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // only wallet pin code script here
        (function() {
            const input = document.getElementById("pinInput"),
                dots = document.querySelectorAll(".pin-dot"),
                container = document.getElementById("pinDots"),
                checkBtn = document.querySelector(".walletPinBtn.icon"),
                checkBtnInner = checkBtn?.querySelector("button"),
                continueBtn = document.querySelector(".continueBtn"),
                backContiBtns = document.querySelectorAll(".walletPinBackconti_btn button");

            // Sync dots, handle enable/disable states, support keyboard + buttons
            const update = (val) => {
                const cleanVal = val.replace(/\D/g, "").slice(0, 6);
                input.value = cleanVal;

                dots.forEach((dot, i) => dot.classList.toggle("filled", i < cleanVal.length));
                const isComplete = cleanVal.length === 6;

                checkBtn?.classList.toggle("disable", !isComplete);
                continueBtn?.classList.toggle("disable", !isComplete);

                backContiBtns.forEach(btn => {
                    isComplete ? btn.removeAttribute("disabled") : btn.setAttribute("disabled", "true");
                });
                checkBtnInner?.toggleAttribute("disabled", !isComplete);
            };

            input.addEventListener("input", () => update(input.value));

            document.querySelectorAll(".walletPinBtn button").forEach(btn => {
                btn.addEventListener("click", () => {
                    const val = input.value;
                    if (btn.querySelector(".fa-xmark")) {
                        update(val.slice(0, -1));
                    } else if (/^\d$/.test(btn.textContent) && val.length < 6) {
                        update(val + btn.textContent);
                    }
                });
            });

            const focusInput = () => setTimeout(() => input.focus(), 0);
            container.addEventListener("click", focusInput);
            window.addEventListener("DOMContentLoaded", focusInput);
            document.body.addEventListener("click", e => {
                if (e.target.id !== "pinInput") focusInput();
            });
        })();
    </script>
@endsection
