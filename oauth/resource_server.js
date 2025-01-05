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

const checkJwt = jwt({
    secret: jwksRsa.expressJwtSecret({
        cache: true,
        rateLimit: true,
        jwksRequestsPerMinute: 5,
        jwksUri: process.env.JWKSURI
    }),
    audience: process.env.API_AUDIENCE,
    issuer: process.env.ISSUER,
    algorithms: [process.env.ALGORITHM]
});
console.log('JWT middleware configured');

const checkScopes = (requiredScopes) => (req, res, next) => {
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