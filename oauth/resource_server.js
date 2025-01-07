const express = require('express');
const cors = require('cors');
const { expressjwt: jwt } = require('express-jwt');
const jwksRsa = require('jwks-rsa');
const jwtAuthz = require('express-jwt-authz');
const bodyParser = require('body-parser');
require('dotenv').config();


const app = express();
app.use(cors());


console.log('Starting the server.js script...');


if (!process.env.AUTH0_DOMAIN || !process.env.API_AUDIENCE) {
    console.error('Error: Missing AUTH0_DOMAIN or API_AUDIENCE in .env file');
    throw 'Ensure you have AUTH0_DOMAIN and API_AUDIENCE in your .env file';
} else {
    console.log('Environment variables loaded successfully');
}


app.use(bodyParser.json());
app.use(bodyParser.urlencoded({
    extended: true
}));
console.log('Body parser middleware configured');


// ===============================================================================
// ||                           CONFIG MIDDLEWARE                               ||
// ===============================================================================
/**
    Richiesta HTTP in ingresso:
    - il middleware intercetta ogni richiesta verso le rotte protette.
    - cerca un token JWT nell'intestazione Authorization della richiesta.
    
    Validazione del token:
    - decodifica e valida il token:
    - verifica che sia stato emesso dall'issuer corretto.
    - verifica che l'audience corrisponda al valore previsto.
    - verifica la firma del token utilizzando la chiave pubblica ottenuta dal JWKS URI.
*/
const checkJwt = jwt({
    /* 
        usiamo la libreria jwks-rsa per gestire automaticamente le chiavi pubbliche necessarie 
        per validare i token firmati.
    */
    secret: jwksRsa.expressJwtSecret({
        cache: true,
        rateLimit: true,
        jwksRequestsPerMinute: 5,
        /*
            risorsa fornita dall'autorità di autenticazione "Auth0" 
            per recuperare le chiavi pubbliche necessarie.
        */
        jwksUri: process.env.JWKSURI 
    }),
    audience: process.env.API_AUDIENCE,     // verifica che il token sia destinato all'API corretta
    issuer: process.env.ISSUER,             // verifica che il token sia stato emesso da un'autorità di autenticazione specifica (Auth0).
    algorithms: [process.env.ALGORITHM]     // specifica l'algoritmo crittografico (RS256) utilizzato per firmare il token.
});
console.log('JWT middleware configured');


/**
 * verifica se il token JWT associato alla richiesta contiene i permessi (o scopes) 
 * necessari per accedere a una risorsa protetta.
 * @param {*} requiredScopes scope richiesti per accedere ad una risorsa
 * @returns errore se gli scope mancano o andiamo avanti se sono corretti.
 */
const checkScopes = (requiredScopes) => (req, res, next) => {
    // accediamo agli scopes contenuti nel token JWT attraverso req.auth.scope.
    const tokenScopes = req.auth.scope?.split(' ') || [];
    
    console.log('Token scopes:', tokenScopes);
    console.log('Required scopes:', requiredScopes);

    const hasScopes = requiredScopes.every(scope => tokenScopes.includes(scope));
    if (!hasScopes) {
        console.warn('Missing required scopes.');
        return res.status(403).send({ 
            message: 'Insufficient scope', 
            missingScopes: requiredScopes.filter(scope => !tokenScopes.includes(scope)) 
        });
    }
    next();
};


// ===============================================================================
// ||                           CONFIG API ENDPOINT                              ||
// ===============================================================================

app.get('/read/datetime', checkJwt, checkScopes(["read:datetime"]), function(req, res) {
    console.log('Authorization Header:', req.headers.authorization);

    const currentTime = new Date().toISOString();
    res.status(200).send({ 
        message: "Current time retrieved successfully",
        currentTime: currentTime
    });
});



const PORT = 8080;
app.listen(PORT, () => {
    console.log(`Server started on http://localhost:${PORT}`);
});