document.addEventListener("DOMContentLoaded", () => {
    const userInfoButton = document.querySelector('#userInfoButton');
    const userInfoContainer = document.querySelector('#user-info-container');
    const userMetadataSection = document.querySelector('#user-metadata');
    const appMetadataSection = document.querySelector('#app-metadata');
    const getDateButton = document.querySelector('.button-info[href="/get_token_api"]');
    const dateDisplay = document.querySelector('.date-display');
    const logoutButton = document.querySelector('.button-logout');

    let userInfoVisible = false;
    let dateVisible = false;

    // "User Info"
    if (userInfoButton) {
        userInfoButton.addEventListener('click', async (event) => {
            event.preventDefault();

            if (userInfoVisible) {
                userInfoContainer.style.display = 'none';
                userMetadataSection.innerHTML = '';
                appMetadataSection.innerHTML = '';
                userInfoVisible = false;
                return;
            }

            try {
                const response = await fetch('/get_user_token');
                if (!response.ok) {
                    throw new Error('Failed to fetch user token and ID');
                }

                const data = await response.json();
                const token = data.token;
                const userId = data.user_id;

                if (!token || !userId) {
                    throw new Error('Token or user ID is missing');
                }

                const apiUrl = `https://dev-s5ht6rjaotiakmet.us.auth0.com/api/v2/users/${userId}`;
                const apiResponse = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (!apiResponse.ok) {
                    throw new Error('Failed to fetch user info from Auth0 API');
                }

                const userData = await apiResponse.json();


                userMetadataSection.innerHTML = `
                    <h3>User Metadata</h3>
                    <pre>${JSON.stringify(userData.user_metadata || {}, null, 4)}</pre>
                `;

                appMetadataSection.innerHTML = `
                    <h3>App Metadata</h3>
                    <pre>${JSON.stringify(userData.app_metadata || {}, null, 4)}</pre>
                `;

                userInfoContainer.style.display = 'block';
                userInfoVisible = true;
            } catch (error) {
                alert(`Error: ${error.message}`);
            }
        });
    }

    // "Get Date"
    if (getDateButton) {
        getDateButton.addEventListener('click', async (event) => {
            event.preventDefault();

            if (dateVisible) {
                dateDisplay.textContent = '';
                dateVisible = false;
                return;
            }

            try {
                const tokenResponse = await fetch('/get_token_api');
                if (!tokenResponse.ok) {
                    throw new Error('Failed to fetch token');
                }
                const tokenData = await tokenResponse.json();
                const jwtToken = tokenData.token;

                const apiResponse = await fetch('http://localhost:8080/read/datetime', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${jwtToken}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (!apiResponse.ok) {
                    throw new Error('Failed to fetch data from API');
                }

                const apiData = await apiResponse.json();

                dateDisplay.textContent = `Current Time: ${apiData.currentTime}`;
                dateVisible = true;
            } catch (error) {
                alert(`Error: ${error.message}`);
            }
        });
    }

    // "Logout"
    if (logoutButton) {
        logoutButton.addEventListener('click', (event) => {
            if (!confirm('Are you sure you want to log out?')) {
                event.preventDefault();
            }
        });
    }
});
