import json
import urllib.request
import urllib.parse
from os import environ as env
from urllib.parse import quote_plus, urlencode
from authlib.integrations.flask_client import OAuth
from dotenv import find_dotenv, load_dotenv
from flask import Flask, redirect, render_template, session, url_for

# Load environment variables
ENV_FILE = find_dotenv()
if ENV_FILE:
    load_dotenv(ENV_FILE)

# Get an access token from Auth0
base_url = f"https://{env.get('AUTH0_DOMAIN')}"
data = urlencode({
    'client_id': env.get("API_CLIENT_ID"),
    'client_secret': env.get("API_CLIENT_SECRET"),
    'audience': env.get("API_AUDIENCE"),
    'grant_type': "client_credentials"
}).encode('utf-8')  # Encode the data to bytes

# Create the request
req = urllib.request.Request(
    url=base_url + "/oauth/token",
    data=data,
    headers={"Content-Type": "application/x-www-form-urlencoded"}
)

# Make the request and parse the response
with urllib.request.urlopen(req) as response:
    resp_body = response.read()
    oauth = json.loads(resp_body)
    access_token = oauth.get('access_token')
    print(access_token)