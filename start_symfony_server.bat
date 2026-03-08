cd /d "%~dp0"

symfony server:stop

:: create a local Certificate Authority (CA)
:: this will allow https
symfony server:ca:install

symfony server:start --allow-all-ip --port=8000
