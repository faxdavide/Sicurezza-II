import json
import urllib.request
import urllib.parse
from os import environ as env
from urllib.parse import quote_plus, urlencode
from authlib.integrations.flask_client import OAuth
from dotenv import find_dotenv, load_dotenv
from flask import Flask, redirect, render_template, session, url_for




# # ===============================================================================
# # ||                           CONFIG ENV & FLASK                              ||
# # ===============================================================================

ENV_FILE = find_dotenv()
if ENV_FILE:
    load_dotenv(ENV_FILE)

app = Flask(__name__)
app.secret_key = env.get("APP_SECRET_KEY")




# # ===============================================================================
# # ||                              CONFIG OAUTH                                 ||
# # ===============================================================================

oauth = OAuth(app)
oauth.register(
    "auth0",
    client_id=env.get("AUTH0_CLIENT_ID"),
    client_secret=env.get("AUTH0_CLIENT_SECRET"),
    client_kwargs={
        "scope": "openid profile email",
    },
    server_metadata_url=f'https://{env.get("AUTH0_DOMAIN")}/.well-known/openid-configuration'
)


@app.route("/login")
def login():
    return oauth.auth0.authorize_redirect(
        redirect_uri=url_for("callback", _external=True)
    )


@app.route("/callback", methods=["GET", "POST"])
def callback():
    token = oauth.auth0.authorize_access_token()
    userinfo = oauth.auth0.parse_id_token(token, nonce=None)
    session["user"] = userinfo

    return redirect("/")


@app.route("/logout")
def logout():
    session.clear()
    return redirect(
        "https://" + env.get("AUTH0_DOMAIN")
        + "/v2/logout?"
        + urlencode(
            {
                "returnTo": url_for("home", _external=True),
                "client_id": env.get("AUTH0_CLIENT_ID"),
            },
            quote_via=quote_plus,
        )
    )


@app.route("/")
def home():
    user = session.get("user")
    # print("Session User:", user)
    return render_template(
        "home.html",
        user=user,
        pretty=json.dumps(user, indent=4) if user else None
    )




# # ===============================================================================
# # ||                           CUSTOM API GET TOKEN                            ||
# # ===============================================================================


@app.route("/get_token_api")
def get_token_api():
    base_url = f"https://{env.get('AUTH0_DOMAIN')}"
    data = urlencode({
        'client_id': env.get("API_CLIENT_ID"),
        'client_secret': env.get("API_CLIENT_SECRET"),
        'audience': env.get("API_AUDIENCE"),
        'grant_type': "client_credentials"
    }).encode('utf-8')

    try:
        req = urllib.request.Request(
            url=base_url + "/oauth/token",
            data=data,
            headers={"Content-Type": "application/x-www-form-urlencoded"}
        )

        with urllib.request.urlopen(req) as response:
            resp_body = response.read()
            oauth = json.loads(resp_body)
            access_token = oauth.get('access_token')

            return {"token": access_token}
    except Exception as e:
        return {"error": str(e)}, 500


@app.route("/get_user_token", methods=["GET"])
def get_user_token():
    base_url = f"https://{env.get('AUTH0_DOMAIN')}"
    data = urlencode({
        'client_id': env.get("AUTH0_CLIENT_ID"),
        'client_secret': env.get("AUTH0_CLIENT_SECRET"),
        'audience': f"https://{env.get('AUTH0_DOMAIN')}/api/v2/",
        'grant_type': "client_credentials"
    }).encode('utf-8')

    try:
        req = urllib.request.Request(
            url=base_url + "/oauth/token",
            data=data,
            headers={"Content-Type": "application/x-www-form-urlencoded"}
        )
        with urllib.request.urlopen(req) as response:
            resp_body = response.read()
            token_data = json.loads(resp_body)
            access_token = token_data.get('access_token')
            if not access_token:
                raise Exception("Failed to retrieve access token")

        user_id = session.get("user", {}).get("sub")
        if not user_id:
            return {"error": "User ID not found in session"}, 403

        return {"token": access_token, "user_id": user_id}
    except Exception as e:
        return {"error": str(e)}, 500
    



# Esegue l'app Flask
if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)