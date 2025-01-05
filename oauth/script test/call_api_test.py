import json
import urllib.request
import urllib.error
from os import environ as env
from dotenv import find_dotenv, load_dotenv

# Load environment variables
ENV_FILE = find_dotenv()
if ENV_FILE:
    load_dotenv(ENV_FILE)

# Define API URL and Access Token
api_url = env.get('API_URL', '')  # Ensure API_URL is set in the .env file
access_token = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6IjJweDl5ZjdFTERGOU5vMVJBRGF4cCJ9.eyJpc3MiOiJodHRwczovL2Rldi1zNWh0NnJqYW90aWFrbWV0LnVzLmF1dGgwLmNvbS8iLCJzdWIiOiI2UkxmZEltd242aGpTWWIwckJ6M2d6cE9kVmZiUlVma0BjbGllbnRzIiwiYXVkIjoiaHR0cHM6Ly9teS1jdXN0b20tYXBpIiwiaWF0IjoxNzM1NzQwMDI0LCJleHAiOjE3MzU4MjY0MjQsInNjb3BlIjoicmVhZDp0ZXN0IiwiZ3R5IjoiY2xpZW50LWNyZWRlbnRpYWxzIiwiYXpwIjoiNlJMZmRJbXduNmhqU1liMHJCejNnenBPZFZmYlJVZmsifQ.CbxmSME6Hjr8r_fngQqliNbREpmIUiQ3KmHXeiEUKVhC_b5CTh6ZDVzNOSg6f0J21Fz4sOHqAXoXlzYWeaeJrBO8u4L50FR8Oxozy1et_lddXkQa9U4yySVpXlj_ll-BAvIbI876psaRIT8SjVSXCk0gyJjs1MDQkrCkWcLtCZ6M8fZTjJEaW10pcelNiFLJmgvrUL8BHo5fELxSrPamuUufx4kAyKpPHOLQVRQfQWgrM-9YWsoIoMEqC0SKTqjfpP4yL9NZ9ey8n9is83h0uMkHR4OvtFIQZNFT2VX8xS4cfmJrvo7wk8xYCQG6mvieWp7I3_JQlgbi52mCO2aSfA"  # Static access token for testing

if not api_url or not access_token:
    raise ValueError("API_URL or ACCESS_TOKEN is not set in the environment variables")

# Prepare the request (GET request as no payload is required)
req = urllib.request.Request(
    api_url,
    headers={
        'Authorization': f'Bearer {access_token}',
        'Content-Type': 'application/json'
    }
)

try:
    # Send the request
    with urllib.request.urlopen(req) as response:
        res = json.loads(response.read().decode('utf-8'))
        print("API Response:")
        print(json.dumps(res, indent=4))
except urllib.error.HTTPError as e:
    print(f"HTTPError: {e.code} {e.reason}")
    print(f"Response: {e.read().decode('utf-8')}")
except urllib.error.URLError as e:
    print(f"URLError: {e.reason}")
except Exception as e:
    print(f"Generic Exception: {str(e)}")