<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome to OverX</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f4f8; font-family: 'Segoe UI', Arial, sans-serif; color: #1a202c; }
        .wrapper { max-width: 600px; margin: 40px auto; }
        .header {
            background: linear-gradient(135deg, #213F7F 0%, #3D6FA8 100%);
            border-radius: 16px 16px 0 0;
            padding: 40px 48px 32px;
            text-align: center;
        }
        .header .logo-text {
            font-size: 32px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .header .logo-text span { color: #70A9DC; }
        .header p {
            margin-top: 8px;
            color: #a0c4e8;
            font-size: 14px;
        }
        .body {
            background: #ffffff;
            padding: 40px 48px;
        }
        .greeting {
            font-size: 22px;
            font-weight: 700;
            color: #213F7F;
            margin-bottom: 12px;
        }
        .intro {
            font-size: 15px;
            color: #4a5568;
            line-height: 1.7;
            margin-bottom: 28px;
        }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #3D6FA8;
            margin-bottom: 12px;
        }
        .credentials-box {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 28px;
        }
        .cred-row {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #edf2f7;
            font-size: 14px;
        }
        .cred-row:last-child { border-bottom: none; }
        .cred-label {
            width: 130px;
            color: #718096;
            font-weight: 600;
            flex-shrink: 0;
        }
        .cred-value {
            color: #1a202c;
            font-weight: 500;
            word-break: break-all;
        }
        .cred-value.password {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: 700;
            color: #213F7F;
            background: #ebf4ff;
            padding: 3px 10px;
            border-radius: 6px;
            letter-spacing: 1px;
        }
        .machines-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 32px;
        }
        .machine-card {
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .machine-card.storing {
            background: linear-gradient(135deg, #ebf4ff, #dbeafe);
            border: 1px solid #bfdbfe;
        }
        .machine-card.cashout {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #bbf7d0;
        }
        .machine-card .count {
            font-size: 36px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 6px;
        }
        .machine-card.storing .count { color: #1d4ed8; }
        .machine-card.cashout .count { color: #16a34a; }
        .machine-card .label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .machine-card.storing .label { color: #3b82f6; }
        .machine-card.cashout .label { color: #22c55e; }
        .machine-card .sublabel {
            font-size: 11px;
            color: #6b7280;
            margin-top: 3px;
        }
        .cta-btn {
            display: block;
            background: linear-gradient(135deg, #3D6FA8, #213F7F);
            color: #fff !important;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            padding: 16px 32px;
            border-radius: 12px;
            margin-bottom: 32px;
            letter-spacing: 0.3px;
        }
        .warning-box {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 10px;
            padding: 14px 18px;
            font-size: 13px;
            color: #92400e;
            margin-bottom: 28px;
            line-height: 1.6;
        }
        .warning-box strong { color: #78350f; }
        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 28px 0;
        }
        .footer {
            background: #f7fafc;
            border-top: 1px solid #e2e8f0;
            border-radius: 0 0 16px 16px;
            padding: 24px 48px;
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
            line-height: 1.8;
        }
        .footer strong { color: #718096; }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- ── Header ──────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="logo-text">Over<span>X</span></div>
        <p>Crypto Mining &amp; Investment Platform</p>
    </div>

    {{-- ── Body ────────────────────────────────────────────────────── --}}
    <div class="body">

        <p class="greeting">Welcome, {{ $client->user->name }}! 👋</p>
        <p class="intro">
            Your account on the <strong>OverX platform</strong> has been created successfully.
            Below you'll find everything you need to get started — please keep this email safe.
        </p>

        {{-- Login Credentials --}}
        <p class="section-title">🔐 Login Credentials</p>
        <div class="credentials-box">
            <div class="cred-row">
                <span class="cred-label">Email</span>
                <span class="cred-value">{{ $client->user->email }}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">Password</span>
                <span class="cred-value password">{{ $plainPassword }}</span>
            </div>
        </div>

        {{-- Machines Summary --}}
        <p class="section-title">⚙ Your Machine Allocation</p>
        <div class="machines-grid">
            <div class="machine-card storing">
                <div class="count">{{ $client->current_storing_machines }}</div>
                <div class="label">Storing</div>
                <div class="sublabel">machines</div>
            </div>
            <div class="machine-card cashout">
                <div class="count">{{ $client->current_cashout_machines }}</div>
                <div class="label">Cashout</div>
                <div class="sublabel">machines</div>
            </div>
        </div>

        {{-- CTA --}}
        <a href="{{ config('app.url') }}" class="cta-btn">
            Login to Your Dashboard →
        </a>

        {{-- Security warning --}}
        <div class="warning-box">
            <strong>⚠ Security notice:</strong> Please change your password immediately after your first login.
            Never share your credentials with anyone. If you did not request this account, contact support right away.
        </div>

        <hr class="divider" />

        <p style="font-size:13px; color:#718096; line-height:1.7;">
            If you have any questions, reply to this email or reach out to your account manager.
            We're happy to help!
        </p>

    </div>

    {{-- ── Footer ──────────────────────────────────────────────────── --}}
    <div class="footer">
        <strong>OverX Platform</strong><br />
        This is an automated email — please do not reply directly.<br />
        &copy; {{ date('Y') }} OverX. All rights reserved.
    </div>

</div>
</body>
</html>
