@extends('layouts.app')

@section('title', $paymentRequest->name)

@section('content')
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="mb-4">
        <a href="{{ route('payments.index') }}" class="text-sm text-muted hover:underline">&larr; {{ __('messages.common.back') }}</a>
    </div>

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl font-semibold">{{ $paymentRequest->name }}</h1>
                @if($paymentRequest->status === 'active')
                    <span class="badge badge-success">{{ __('messages.payments.status_active') }}</span>
                @elseif($paymentRequest->status === 'closed')
                    <span class="badge badge-gray">{{ __('messages.payments.status_closed') }}</span>
                @else
                    <span class="badge badge-danger">{{ __('messages.payments.status_cancelled') }}</span>
                @endif
            </div>
            @if($isAdmin && $paymentRequest->status === 'active')
                <div class="flex items-center gap-2">
                    <a href="{{ route('payments.edit', $paymentRequest) }}" class="btn-secondary text-sm">{{ __('messages.common.edit') }}</a>
                    <form action="{{ route('payments.cancel-request', $paymentRequest) }}" method="POST"
                        onsubmit="return confirm('{{ __('messages.payments.cancel_request_confirm') }}')">
                        @csrf
                        <button type="submit" class="btn-danger text-sm">{{ __('messages.payments.cancel_request') }}</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Info --}}
    <div class="card mb-6">
        <div class="card-body space-y-3">
            @if($paymentRequest->description)
                <div>
                    <p class="text-muted">{{ $paymentRequest->description }}</p>
                </div>
            @endif
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 140px;">{{ __('messages.payments.amount') }}:</span>
                <span>{{ number_format($paymentRequest->amount, 0, ',', ' ') }} {{ $paymentRequest->currency }}</span>
            </div>
            <div class="flex gap-2">
                <span class="font-medium" style="min-width: 140px;">{{ __('messages.payments.due_date') }}:</span>
                <span>{{ app()->getLocale() === 'cs' ? $paymentRequest->due_date->format('d.m.Y') : $paymentRequest->due_date->format('Y-m-d') }}</span>
            </div>
            @if($paymentRequest->team)
                <div class="flex gap-2">
                    <span class="font-medium" style="min-width: 140px;">{{ __('messages.events.team') }}:</span>
                    <span>{{ $paymentRequest->team->name }}</span>
                </div>
            @endif
            @if($paymentRequest->bank_account)
                <div class="flex gap-2">
                    <span class="font-medium" style="min-width: 140px;">{{ __('messages.payments.bank_account') }}:</span>
                    <span class="font-mono">{{ $paymentRequest->bank_account }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Member Payments --}}
    @if($isAdmin)
        <div class="card mb-6">
            <div class="card-header">
                <div class="flex items-center justify-between">
                    <h2 class="font-medium">
                        {{ __('messages.payments.member_payments') }}
                        <span class="text-muted font-normal text-sm">
                            ({{ $paymentRequest->memberPayments->where('status', 'paid')->count() }}/{{ $paymentRequest->memberPayments->count() }} {{ __('messages.payments.paid') }})
                        </span>
                    </h2>
                </div>
            </div>
            <div class="card-body">
                @if($paymentRequest->memberPayments->isEmpty())
                    <p class="text-muted">{{ __('messages.common.no_results') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border text-left">
                                    <th class="pb-2 font-medium">{{ __('messages.club_admin.member_name') }}</th>
                                    <th class="pb-2 font-medium">{{ __('messages.payments.variable_symbol') }}</th>
                                    <th class="pb-2 font-medium">{{ __('messages.payments.amount') }}</th>
                                    <th class="pb-2 font-medium">{{ __('messages.payments.status') }}</th>
                                    <th class="pb-2 font-medium">{{ __('messages.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentRequest->memberPayments->sortBy(fn ($p) => $p->user->last_name) as $mp)
                                    <tr class="border-b border-border last:border-0">
                                        <td class="py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center text-xs font-medium shrink-0">
                                                    {{ strtoupper(mb_substr($mp->user->first_name, 0, 1)) }}{{ strtoupper(mb_substr($mp->user->last_name, 0, 1)) }}
                                                </div>
                                                <span class="font-medium">{{ $mp->user->full_name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 font-mono text-muted">{{ $mp->variable_symbol }}</td>
                                        <td class="py-3">{{ number_format($mp->amount, 0, ',', ' ') }} {{ $paymentRequest->currency }}</td>
                                        <td class="py-3">
                                            @if($mp->status === 'pending')
                                                <span class="badge badge-warning">{{ __('messages.payments.pending') }}</span>
                                            @elseif($mp->status === 'paid')
                                                <span class="badge badge-success">{{ __('messages.payments.paid') }}</span>
                                            @elseif($mp->status === 'overdue')
                                                <span class="badge badge-danger">{{ __('messages.payments.overdue') }}</span>
                                            @else
                                                <span class="badge badge-gray">{{ __('messages.payments.cancelled') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @if($mp->status === 'pending' || $mp->status === 'overdue')
                                                <form action="{{ route('payments.confirm', $mp) }}" method="POST" class="inline"
                                                    onsubmit="return confirm('{{ __('messages.payments.confirm_mark_paid') }}')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn-ghost text-success text-sm">{{ __('messages.payments.mark_paid') }}</button>
                                                </form>
                                            @elseif($mp->status === 'paid')
                                                <span class="text-xs text-muted">
                                                    {{ $mp->paid_at ? (app()->getLocale() === 'cs' ? $mp->paid_at->format('d.m.Y') : $mp->paid_at->format('Y-m-d')) : '' }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Member view: show own payment + QR --}}
        @php
            $myPayment = $paymentRequest->memberPayments->firstWhere('user_id', auth()->id());
        @endphp
        @if($myPayment)
            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="font-medium">{{ __('messages.payments.your_payment') }}</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="flex items-center gap-3">
                        @if($myPayment->status === 'pending')
                            <span class="badge badge-warning">{{ __('messages.payments.pending') }}</span>
                        @elseif($myPayment->status === 'paid')
                            <span class="badge badge-success">{{ __('messages.payments.paid') }}</span>
                        @elseif($myPayment->status === 'overdue')
                            <span class="badge badge-danger">{{ __('messages.payments.overdue') }}</span>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <span class="font-medium" style="min-width: 140px;">{{ __('messages.payments.amount') }}:</span>
                        <span class="font-semibold">{{ number_format($myPayment->amount, 0, ',', ' ') }} {{ $paymentRequest->currency }}</span>
                    </div>

                    @if($myPayment->variable_symbol)
                        <div class="flex gap-2">
                            <span class="font-medium" style="min-width: 140px;">{{ __('messages.payments.variable_symbol') }}:</span>
                            <span class="font-mono">{{ $myPayment->variable_symbol }}</span>
                        </div>
                    @endif

                    @if($myPayment->qr_payload && $myPayment->status !== 'paid')
                        <div class="mt-4 p-4 bg-bg rounded-xl text-center">
                            <p class="text-sm text-muted mb-3">{{ __('messages.payments.qr_instructions') }}</p>
                            <div class="inline-block p-4 bg-surface rounded-lg border border-border">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($myPayment->qr_payload) }}"
                                    alt="QR {{ __('messages.payments.payment_code') }}"
                                    width="200" height="200"
                                    class="mx-auto">
                            </div>
                            <p class="text-xs text-muted mt-2 font-mono break-all">{{ $myPayment->qr_payload }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif
@endsection
