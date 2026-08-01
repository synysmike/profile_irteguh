<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Invoice {{ $invoiceNumber }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(160deg, #f8fafc 0%, #ede9fe 50%, #f1f5f9 100%);
            color: #1f2937;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);
            padding: 28px 24px;
            border: 1px solid #e5e7eb;
        }
        .brand {
            text-align: center;
            margin-bottom: 20px;
        }
        .brand h1 {
            margin: 0 0 4px;
            font-size: 18px;
            color: #111827;
        }
        .brand p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
        }
        .meta {
            background: #f9fafb;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 13px;
            color: #4b5563;
        }
        .meta strong { color: #111827; }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #374151;
        }
        input[type="tel"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 16px;
            margin-bottom: 8px;
        }
        input[type="tel"]:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }
        .hint {
            font-size: 12px;
            color: #6b7280;
            margin: 0 0 16px;
        }
        button {
            width: 100%;
            padding: 12px 16px;
            background: #7c3aed;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #6d28d9; }
        .alert {
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 14px;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .alert-warn {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <h1>{{ \App\Models\Setting::appName() }}</h1>
            <p>Verifikasi akses invoice</p>
        </div>

        <div class="meta">
            <div>No. Faktur: <strong>{{ $invoiceNumber }}</strong></div>
            @if(!empty($customerName))
            <div style="margin-top:4px;">Pelanggan: <strong>{{ $customerName }}</strong></div>
            @endif
        </div>

        @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if(!$hasCustomerPhone)
        <div class="alert alert-warn">
            Nomor HP customer belum tercatat pada invoice ini. Hubungi admin untuk membuka invoice.
        </div>
        @else
        <form method="POST" action="{{ $verifyRoute }}">
            @csrf
            <label for="phone">Nomor HP pelanggan</label>
            <input
                type="tel"
                id="phone"
                name="phone"
                value="{{ old('phone') }}"
                placeholder="08xxxxxxxxxx"
                required
                autocomplete="tel"
                inputmode="tel"
            >
            <p class="hint">Masukkan nomor HP yang terdaftar pada invoice (boleh format 08… atau 62…).</p>
            @error('phone')
            <div class="alert alert-error">{{ $message }}</div>
            @enderror
            <button type="submit">Buka Invoice</button>
        </form>
        @endif
    </div>
</body>
</html>
