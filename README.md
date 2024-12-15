Ecco una spiegazione dettagliata di alcuni comandi utilizzati nella configurazione Apache SSL:

# **Apache SSL Configuration Guide**

## **Comandi utilizzati nella configurazione**
Questa guida spiega il significato di ogni comando usato nella configurazione di Apache per gestire connessioni HTTPS con SSL/TLS e certificati client.

---

### **VirtualHost**

- **`<VirtualHost *:443>`**
  - Specifica che questo Virtual Host gestirà il traffico HTTPS sulla porta **443**.

- **`ServerName www.giornalisti_cn.it`**
  - Definisce il nome principale del sito che risponderà a questo Virtual Host.

- **`ServerAlias giornalisti_cn.it`**
  - Aggiunge un alias alternativo per il sito, consentendo l'accesso anche con il nome specificato.

---

### **DocumentRoot**

- **`DocumentRoot /var/www/html/giornalisti`**
  - Imposta la directory principale da cui Apache servirà i file del sito.

---

### **SSL Configuration**

- **`SSLEngine on`**
  - Abilita il motore SSL/TLS per gestire connessioni HTTPS.

- **`SSLProtocol TLSv1.2`**
  - Specifica il protocollo SSL/TLS da utilizzare. In questo caso, è forzato l'uso di **TLS 1.2** per garantire maggiore sicurezza.

---

### **Certificati**

- **`SSLCertificateFile /etc/apache2/myconf/server/server.crt`**
  - Specifica il percorso del certificato SSL utilizzato dal server.

- **`SSLCertificateKeyFile /etc/apache2/myconf/server/server.key`**
  - Specifica il percorso della chiave privata associata al certificato del server.

- **`SSLCertificateChainFile /etc/apache2/myconf/chain.crt`**
  - Fornisce la catena di certificati intermedi (Subordinate CA) necessaria per completare la fiducia tra il certificato del server e la Root CA.

- **`SSLCACertificateFile /etc/apache2/myconf/rootCA/rootCA.crt`**
  - Specifica il file contenente il certificato della Root CA, utilizzato per verificare i certificati client.

---

### **Autenticazione del certificato client**

- **`SSLVerifyClient require`**
  - Richiede obbligatoriamente che il client presenti un certificato valido per accedere al server.

- **`SSLVerifyDepth 2`**
  - Imposta la profondità massima per la verifica della catena dei certificati:
    - **1**: Verifica solo la Root CA.
    - **2**: Verifica Root CA → Sub CA → Certificato Client.

---

### **Opzioni SSL**

- **`SSLOptions +OptRenegotiate`**
  - Abilita la possibilità di una nuova negoziazione SSL, necessaria in alcuni casi per gestire configurazioni avanzate come l'autenticazione client.

- **`SSLOptions +StdEnvVars +ExportCertData`**
  - **`+StdEnvVars`**: Esporta variabili SSL standard nell'ambiente, come `SSL_CLIENT_S_DN`, che può essere usata da PHP o altri linguaggi lato server.
  - **`+ExportCertData`**: Esporta il certificato completo del client (in formato PEM) nell'ambiente.

---

### **Controllo sull'accesso**

- **`SSLRequire %{SSL_CLIENT_S_DN_CN} eq "Davide" || %{SSL_CLIENT_S_DN_CN} eq "Mario"`**
  - Specifica che l'accesso è consentito solo ai client il cui certificato contiene un **Common Name (CN)** uguale a "Davide" o "Mario".

---

### **Logging**

- **`ErrorLog ${APACHE_LOG_DIR}/error.log`**
  - Specifica il file di log per registrare gli errori relativi a questo Virtual Host.

- **`CustomLog ${APACHE_LOG_DIR}/access.log combined`**
  - Specifica il file di log per registrare tutte le richieste di accesso al Virtual Host, utilizzando il formato standard **combined** (incluso IP, User-Agent, ecc.).

---

## **Configurazione completa di esempio**

```apache
<VirtualHost *:443>
    ServerName www.giornalisti_cn.it
    ServerAlias giornalisti_cn.it

    DocumentRoot /var/www/html/giornalisti

    SSLEngine on
    SSLProtocol TLSv1.2

    SSLCertificateFile /etc/apache2/myconf/server/server.crt
    SSLCertificateKeyFile /etc/apache2/myconf/server/server.key
    SSLCertificateChainFile /etc/apache2/myconf/chain.crt
    SSLCACertificateFile /etc/apache2/myconf/rootCA/rootCA.crt

    SSLVerifyClient require
    SSLVerifyDepth 2
    SSLOptions +OptRenegotiate

    <Directory /var/www/html/giornalisti>
        SSLRequire %{SSL_CLIENT_S_DN_CN} eq "Davide" || %{SSL_CLIENT_S_DN_CN} eq "Mario"
        SSLOptions +StdEnvVars +ExportCertData
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

---

## **Note aggiuntive**
- **`SSLVerifyClient`**: Può essere impostato su `optional` se vuoi che il certificato client sia opzionale.
- **`SSLProtocol`**: Può includere più protocolli separati da spazi, ad esempio: `TLSv1.2 TLSv1.3`.

---

Con questa guida, hai una documentazione chiara e dettagliata sui comandi utilizzati nella configurazione di Apache per SSL/TLS e certificati client. Se hai bisogno di ulteriori dettagli, fammi sapere! 🚀
