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
        h1, h2 {
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
        .it-department {
            color: red;
        }
        .hr-department {
            color: green;
        }
        .default-department {
            color: blue;
        }
    </style>
</head>
<body>
    <?php
    // Configurazione LDAP
    $ldap_server = "ldap://10.10.0.8";
    $ldap_dn = "cn=admin,dc=giornalisti,dc=org"; // Distinguished Name per LDAP Admin
    $ldap_password = "admin";
    $ldap_base_dn = "dc=giornalisti,dc=org";

    // Connessione LDAP
    $ldap_conn = ldap_connect($ldap_server);
    if (!$ldap_conn) {
        die("Errore: Impossibile connettersi al server LDAP.");
    }
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);

    // Bind LDAP
    if (!ldap_bind($ldap_conn, $ldap_dn, $ldap_password)) {
        die("Errore: Autenticazione LDAP fallita.");
    }

    // Ricerca LDAP per certificati
    $filter = "(userCertificate;binary=*)"; // Filtro per recuperare tutte le entry con certificato
    $attributes = ["dn", "cn", "userCertificate;binary"]; // Attributi da recuperare
    $search = ldap_search($ldap_conn, $ldap_base_dn, $filter, $attributes);

    if (!$search) {
        die("Errore: Ricerca LDAP fallita.");
    }

    $entries = ldap_get_entries($ldap_conn, $search);
    if ($entries["count"] == 0) {
        die("Errore: Nessuna entry LDAP con attributo userCertificate trovata.");
    }

    // Ricezione del certificato client da Apache
    $client_cert_pem = $_SERVER['SSL_CLIENT_CERT'] ?? null;
    if (!$client_cert_pem) {
        die("Errore: Certificato client non fornito.");
    }

    // Converti il certificato client in Base64
    $client_cert_pem = trim($client_cert_pem); // Rimuove spazi inutili
    $cert_base64 = preg_replace('/-----.*-----/', '', $client_cert_pem); // Rimuove intestazioni PEM
    $client_cert_der_data = base64_decode($cert_base64); // Decodifica il contenuto Base64
    $client_cert_der_data = base64_encode($client_cert_der_data); // Riconverti in Base64 per LDAP

    // Confronta il certificato client con quelli salvati in LDAP
    $authenticated = false; // Flag per autenticazione
    $authenticated_user = null; // Nome dell'utente autenticato
    $department = null; // Dipartimento dell'utente

    foreach ($entries as $entry) {
        if (isset($entry["usercertificate;binary"]) && $client_cert_der_data === base64_encode($entry["usercertificate;binary"][0])) {
            $authenticated = true;
            $authenticated_user = $entry["cn"][0];
            preg_match('/ou=([^,]+)/i', $entry["dn"], $matches);
            $department = $matches[1] ?? null;
            break;
        }
    }

    // Determina il colore per il dipartimento
    $department_class = "default-department";
    if ($department === "IT Department") {
        $department_class = "it-department";
    } elseif ($department === "HR Department") {
        $department_class = "hr-department";
    }

    // Messaggio di benvenuto o errore
    if ($authenticated) {
        $cn = htmlspecialchars($authenticated_user);
        echo "<h1>Benvenuto, $cn!</h1>";
        if ($department) {
            echo "<h2>Dipartimento: <span class='$department_class'>$department</span></h2>";
        }
    } else {
        die("<h3>Errore: Certificato non riconosciuto.</h3><p>Il certificato fornito non corrisponde a nessun utente LDAP.</p>");
    }
    ?>
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
