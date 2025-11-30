@extends('layouts.app')

@section('content')
<div class="container">
    <h3>إعداد المصادقة الثنائية</h3>

    {{-- إذا غير مفعلة --}}
    @if(!auth()->user()->two_factor_enabled)
        <div>
            <h4>امسحي هذا الرمز في تطبيق المصادقة:</h4>

            @if(isset($qrSvg))
                <div>{!! $qrSvg !!}</div>
            @endif

            @if(isset($secret))
                <p>أو أدخلي المفتاح يدويًا: <strong>{{ $secret }}</strong></p>
            @endif
        </div>

        <form method="POST" action="{{ route('2fa.enable') }}">
            @csrf
            <label>أدخلي الرمز من التطبيق:</label>
            <input type="text" name="totp_code" required>
            <button type="submit">تفعيل</button>
            <a href="{{ url()->previous() }}" class="btn">إلغاء</a>
        </form>
    @else
        {{-- إذا مفعلة --}}
        <form method="POST" action="{{ route('2fa.disable') }}">
            @csrf
            <button type="submit" style="background:#b00;color:#fff">تعطيل المصادقة الثنائية</button>
            <a href="{{ url()->previous() }}" class="btn">إلغاء</a>
        </form>

        @if(is_array(auth()->user()->two_factor_recovery_codes))
            <h4>الرموز الاحتياطية:</h4>
            <ul>
                @foreach(auth()->user()->two_factor_recovery_codes as $code)
                    <li>{{ $code }}</li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
@endsection