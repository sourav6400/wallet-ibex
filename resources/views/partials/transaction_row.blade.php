@php
    $hashShort = formatAddress($hash);
    $fromShort = formatAddress($from);
    $toShort = formatAddress($to);
    $dateTime = formatTimestamp($timestamp);
@endphp

<tr>
    <td><div class="value_data"><h5>{{ $sl }}</h5></div></td>
    <td>
        <div class="value_data">
            <div class="flex-center">
                <h5>{{ $hashShort }}</h5>
                <button onclick="copyToClipboard('{{ $hash }}', this)" class="copy-btn" title="Copy full address">
                    <i class="fas fa-copy"></i>
                </button>
                <span class="copy-alert">Copied!</span>
            </div>
        </div>
    </td>
    <td><div class="value_data"><h5>{{ $blockNumber }}</h5></div></td>
    <td>
        <div class="value_data">
            <div class="flex-center">
                <h5>{{ $fromShort }}</h5>
                <button onclick="copyToClipboard('{{ $from }}', this)" class="copy-btn" title="Copy full address">
                    <i class="fas fa-copy"></i>
                </button>
                <span class="copy-alert">Copied!</span>
            </div>
        </div>
    </td>
    <td>
        <div class="value_data">
            <div class="flex-center">
                <h5>{{ $toShort }}</h5>
                <button onclick="copyToClipboard('{{ $to }}', this)" class="copy-btn" title="Copy full address">
                    <i class="fas fa-copy"></i>
                </button>
                <span class="copy-alert">Copied!</span>
            </div>
        </div>
    </td>
    @if(!request()->routeIs('transactions'))
    <td><div class="value_data"><h5>{{ $type }}</h5></div></td>
    @endif
    <td><div class="value_data"><h5>{{ number_format($amount, 6, '.', '') }} {{ $symbol }}</h5></div></td>
    <td><div class="value_data"><h5>{{ $dateTime }}</h5></div></td>
</tr>
