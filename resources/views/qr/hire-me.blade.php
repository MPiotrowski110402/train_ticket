<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zatrudnij mnie please</title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        img {
            max-width: min(100%, 900px);
            max-height: 92vh;
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.45);
        }
    </style>
</head>
<body>
    <img
        src="{{ asset('assets/memes/wazne.png') }}"
        alt="Zatrudnij mnie please"
    >
</body>
</html>