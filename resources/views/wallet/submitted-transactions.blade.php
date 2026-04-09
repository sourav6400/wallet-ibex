@extends('layouts.app')
@section('content')
    <div class="dashboardRightMain_body">
        <div class="transaction_body_wrapper">
            <div class="transaction_title v3">
                <h3>Submitted Transactions Status</h3>
            </div>
        </div>

        <div class="coinAssetTable_wrapper">
            <div class="coinAsset_table">
                <div class="mt-4 mb-4">
                    <table id="dataTable">
                        <thead>
                            <tr>
                                <th>SL#</th>
                                <th>Token</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Submitted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($submittedTransactions as $index => $transaction)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $transaction->token }}</td>
                                    <td>{{ $transaction->from }}</td>
                                    <td>{{ $transaction->to }}</td>
                                    <td>{{ $transaction->amount }}</td>
                                    <td>{{ ucfirst($transaction->status ?? 'pending') }}</td>
                                    <td>{{ $transaction->created_at ? $transaction->created_at->format('M d, Y h:i A') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No submitted transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
