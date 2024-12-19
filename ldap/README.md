Ecco un esempio di file `README.md` per una guida sui comandi LDAP più utilizzati:

```markdown
# Guida ai Comandi LDAP

Questa guida contiene i comandi LDAP più comuni per la gestione e l'interrogazione di un server LDAP.

## Connessione al Server LDAP

### Testare la connessione
```bash
ldapsearch -x -H ldap://<server> -s base -b "" "objectClass=*"
```

### Autenticazione con credenziali
```bash
ldapsearch -x -H ldap://<server> -D "cn=admin,dc=example,dc=com" -w <password>
```

---

## Ricerca di Utenti e Oggetti

### Cercare tutti gli oggetti
```bash
ldapsearch -x -b "dc=example,dc=com" "(objectClass=*)"
```

### Cercare un utente specifico
```bash
ldapsearch -x -b "dc=example,dc=com" "(uid=johndoe)"
```

### Cercare utenti in un gruppo
```bash
ldapsearch -x -b "dc=example,dc=com" "(memberOf=cn=developers,ou=groups,dc=example,dc=com)"
```

---

## Aggiungere, Modificare ed Eliminare Oggetti

### Aggiungere un nuovo oggetto
1. Creare un file LDIF (`utente.ldif`):
    ```ldif
    dn: uid=johndoe,ou=users,dc=example,dc=com
    objectClass: inetOrgPerson
    uid: johndoe
    cn: John Doe
    sn: Doe
    userPassword: password123
    ```
2. Utilizzare il comando:
    ```bash
    ldapadd -x -D "cn=admin,dc=example,dc=com" -w <password> -f utente.ldif
    ```

### Modificare un oggetto
1. Creare un file LDIF (`modifica.ldif`):
    ```ldif
    dn: uid=johndoe,ou=users,dc=example,dc=com
    changetype: modify
    replace: mail
    mail: johndoe@example.com
    ```
2. Eseguire:
    ```bash
    ldapmodify -x -D "cn=admin,dc=example,dc=com" -w <password> -f modifica.ldif
    ```

### Eliminare un oggetto
```bash
ldapdelete -x -D "cn=admin,dc=example,dc=com" -w <password> "uid=johndoe,ou=users,dc=example,dc=com"
```

---

## Gestione di Password

### Reimpostare una password
1. Creare un file LDIF (`reset_password.ldif`):
    ```ldif
    dn: uid=johndoe,ou=users,dc=example,dc=com
    changetype: modify
    replace: userPassword
    userPassword: nuovaPassword
    ```
2. Utilizzare:
    ```bash
    ldapmodify -x -D "cn=admin,dc=example,dc=com" -w <password> -f reset_password.ldif
    ```

---
