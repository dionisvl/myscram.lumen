# SCRAM Authentication Guide (MySQL)
## Client/Server version

This is a test implementation of SCRAM authentication using PHP/JS and Laravel (Lumen).

1. The client sends a request to the server to obtain the server_nonce.
2. Upon receiving the nonce, the client generates the client_proof and sends it to the server.
3. The server verifies the authorization based on the server_nonce and client_proof and responds to the client with the authentication result.

Note: The nonce is generated on the server, stored in the database, and immediately removed after the authentication attempt.

Test login for the database: test_login
Test password: test_login, hashed with sha256: b3e1e614c321bc20e47b0a260e3c4e3f3a91875d5e71eb2fd85b76f939412115 (HELLO_WORLD)
