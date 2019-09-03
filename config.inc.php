<?php 

//show php errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

//activate login
define('LOGINPASSWORD','mypassword');
include 'login.php';

//set configurations CryptoLab
date_default_timezone_set('Europe/Amsterdam');
define('API_KEY_CRYPTOCOMPARE','--Paste your key from https://min-api.cryptocompare.com/ here--');

//round to zero if value aproaches zero with X decimals.
define('EUR',2);
define('USD',2);
define('BTC',8);  //btc has 8 decimal places
define('ETH',4);  //eth has 18 decimal places
define('LTC',4);  //ltc has 8 decimal places
define('XMR',4);  //xmr has 12 decimal places
define('DASH',3);
define('ETC',4);
define('BCH',4);
define('BSV',4);

require_once('class.cryptolab.php');

?>