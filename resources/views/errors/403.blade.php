<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Dibatasi</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff;
            color: #181d18;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .card {
            background-color: #ffffff;
            border: 1px solid #bfc9bd;
            border-radius: 16px;
            padding: 32px 24px;
            max-width: 380px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
        }
        .icon {
            background-color: #fdf2f2;
            color: #ba1a1a;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
            font-size: 28px;
        }
        h1 {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: #181d18;
        }
        p {
            font-size: 13px;
            color: #5c5f61;
            margin: 0 0 24px 0;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            background-color: #004c22;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .btn:hover {
            background-color: #003316;
            transform: translateY(-1px);
        }
        .btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">403</div>
        <h1>Akses Dibatasi</h1>
        <p>Maaf, Anda tidak memiliki izin atau otoritas yang diperlukan untuk membuka halaman ini.</p>
        <a href="{{ url('/') }}" class="btn">Kembali ke Beranda</a>
    </div>
</body>
</html>
