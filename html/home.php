<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informazioni Certificato Client</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #444;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th, table td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f4f4f4;
            color: #555;
        }
        pre {
            background: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Informazioni del Certificato Client</h1>
    <div class="container">

        <h2>Informazioni Generali del Certificato</h2>
        <table>
            <tr><th>Nome Comune (CN)</th><td><?= htmlspecialchars($_SERVER['SSL_CLIENT_S_DN_CN'] ?? 'N/A') ?></td></tr>
            <tr><th>Organizzazione (O)</th><td><?= htmlspecialchars($_SERVER['SSL_CLIENT_S_DN_O'] ?? 'N/A') ?></td></tr>
            <tr><th>Unità Organizzativa (OU)</th><td><?= htmlspecialchars($_SERVER['SSL_CLIENT_S_DN_OU'] ?? 'N/A') ?></td></tr>
            <tr><th>Paese (C)</th><td><?= htmlspecialchars($_SERVER['SSL_CLIENT_S_DN_C'] ?? 'N/A') ?></td></tr>
            <tr><th>Emittente (Issuer DN)</th><td><?= htmlspecialchars($_SERVER['SSL_CLIENT_I_DN'] ?? 'N/A') ?></td></tr>
            <tr><th>Numero di Serie</th><td><?= htmlspecialchars($_SERVER['SSL_CLIENT_M_SERIAL'] ?? 'N/A') ?></td></tr>
            <tr><th>Valido Dal</th><td><?= htmlspecialchars($_SERVER['SSL_CLIENT_V_START'] ?? 'N/A') ?></td></tr>
            <tr><th>Valido Fino</th><td><?= htmlspecialchars($_SERVER['SSL_CLIENT_V_END'] ?? 'N/A') ?></td></tr>
        </table>

        <h2>Certificato Client Completo</h2>
        <pre><?= htmlspecialchars($_SERVER['SSL_CLIENT_CERT'] ?? 'N/A') ?></pre>

        <h2>Variabili SSL Esportate</h2>
        <pre><?php
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'SSL_') === 0) {
                    echo htmlspecialchars($key . ": " . $value) . "\n";
                }
            }
        ?></pre>
    </div>
</body>
</html>
