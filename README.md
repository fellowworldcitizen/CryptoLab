# CryptoLab
Portfolio tracking

## How to use
First, create an API key at: https://min-api.cryptocompare.com/
*(This is only used to get history price data, eg: 1EUR=0.001057BTC on 01-01-2017. None of your actual ownings get shared.)*

### Webserver
1. Upload all files to a webserver that supports PHP.
1. Change the API key in the config.inc.php file.
1. Change the login password
1. Run the index.php file.

### Mac OS
1. Download/Clone all files to a folder.
1. Change the API key in the config.inc.php file.
1. Change the login password OR disable login by commenting out the include login.php file.
1. Open the Terminal app.
1. Enter: cd /user/Desktop/CryptoLab *(Change the location where you stored the folder)*
1. Enter: php -S 127.0.0.1:8080 *(this will start the PHP server)*
1. Browse to localhost:8080

### Installation problems
Delete all files from the cache folder and run again.

## Third parties
price data from https://min-api.cryptocompare.com/
icons by http://cryptoicons.co/

## Donate
IF you enjoy this, please consider a donation:

Bitcoin: 39MAk7u3nTeJxifeRUidk9sv18oqFD6ECZ

Monero: 48ZtrWdgrY9EgLjnn5XY2uRuUZ4GaivBsaswpgJuXLbzjHTYKkKq1Mc6nKvdRheMWg8YhNLQ3uzRiUcRz6JTrPiq1sStq12
