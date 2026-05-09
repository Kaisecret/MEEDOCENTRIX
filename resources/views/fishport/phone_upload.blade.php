<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Phone Upload | Meedocentrix</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #155f8f;
            --primary-soft: #eff6ff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #dbe7f3;
            --bg: #f1f5f9;
            --ok: #065f46;
            --ok-bg: #ecfdf5;
            --err: #9f1239;
            --err-bg: #fff1f2;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            color: var(--text);
            background: linear-gradient(180deg, #f8fbff 0%, var(--bg) 100%);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 16px;
        }

        .pu-card {
            width: min(560px, 100%);
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .pu-head {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border);
            background: #f8fafc;
        }

        .pu-head h1 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
            color: #0b3150;
        }

        .pu-body {
            padding: 16px 18px 18px;
            display: grid;
            gap: 12px;
        }

        .pu-note {
            margin: 0;
            color: var(--muted);
            font-size: 0.93rem;
            line-height: 1.45;
        }

        .pu-doc {
            margin: 0;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--primary-soft);
            color: #0b3f65;
            font-weight: 700;
            font-size: 0.92rem;
        }

        .pu-file {
            width: 100%;
            min-height: 42px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 8px;
            background: #fff;
        }

        .pu-actions {
            display: flex;
            justify-content: flex-end;
        }

        .pu-btn {
            border: 1px solid var(--primary);
            background: var(--primary);
            color: #fff;
            border-radius: 10px;
            min-height: 42px;
            padding: 0 16px;
            font-size: 0.92rem;
            font-weight: 700;
        }

        .pu-alert {
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .pu-alert-ok { background: var(--ok-bg); color: var(--ok); border: 1px solid #86efac; }
        .pu-alert-err { background: var(--err-bg); color: var(--err); border: 1px solid #fecdd3; }
    </style>
</head>
<body>
    <main class="pu-card">
        <header class="pu-head">
            <h1>Phone Upload</h1>
        </header>
        <section class="pu-body">
            @if ($expired)
                <div class="pu-alert pu-alert-err">{{ $message ?? 'This upload session is no longer valid.' }}</div>
            @else
                @if (session('status'))
                    <div class="pu-alert pu-alert-ok">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="pu-alert pu-alert-err">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="pu-alert pu-alert-err">{{ $errors->first() }}</div>
                @endif

                <p class="pu-note">Capture a photo or choose an existing file, then upload it. Return to your computer and wait for auto-attach.</p>
                <p class="pu-doc">{{ $docLabel }}</p>

                <form method="POST" action="{{ route('fishport.phone_upload.upload', ['token' => $token]) }}" enctype="multipart/form-data">
                    @csrf
                    <input
                        type="file"
                        name="document"
                        class="pu-file"
                        accept="image/*,application/pdf,.jpg,.jpeg,.png,.pdf"
                        capture="environment"
                        required
                    >
                    <div class="pu-actions" style="margin-top:12px;">
                        <button class="pu-btn" type="submit">Upload To Computer Form</button>
                    </div>
                </form>

                @if ($uploaded)
                    <p class="pu-note">A file is already uploaded for this session. Uploading again will replace the previous file.</p>
                @endif
            @endif
        </section>
    </main>
</body>
</html>

