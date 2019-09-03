<?php 

class Holding
{
	var $date;
	var $holdings;
	var $holdings_per_exchange;
	var $transaction;
	var $investment;
	var $total_btc;
	var $total_eur;
	var $total_usd;

	public function __construct($date, $holdings, $holdings_per_exchange, $investment, $transaction=false) {
		$this->date = $date;
		$this->holdings = $holdings;
		$this->holdings_per_exchange = $holdings_per_exchange;
		$this->transaction = $transaction;
		$this->investment = $investment;

		//request historic price data for given date
		$total_btc = 0;
		$total_eur = 0;
		$total_usd = 0;
		if(defined('API_KEY_CRYPTOCOMPARE')){
			$timestamp = strtotime($date);
			foreach ($this->holdings as $cur=>$val)
			{
				$filename = 'cache/'.$cur.'_'.$timestamp.'.txt';
				if(!file_exists($filename)){
					//request data
					$url = 'https://min-api.cryptocompare.com/data/pricehistorical?fsym='.$cur.'&tsyms=BTC,USD,EUR&ts='.$timestamp.'&api_key='.API_KEY_CRYPTOCOMPARE;
					$json = file_get_contents($url);
						
					$myfile = fopen($filename, "w") or die("Unable to open file!");
					fwrite($myfile, $json);
					fclose($myfile);
				}
				$json = file_get_contents($filename);
				$pricedata=json_decode($json,true);
	
				$total_btc += ($pricedata[$cur]['BTC']*$val);
				$total_eur += ($pricedata[$cur]['EUR']*$val);
				$total_usd += ($pricedata[$cur]['USD']*$val);
			}
		}
		
		$this->total_btc = $total_btc;
		$this->total_eur = $total_eur;
		$this->total_usd = $total_usd;
	}

	public function getDate(){
		return $this->date;
	}

	public function getHoldings()
	{
		return $this->holdings;
	}

	public function getHoldingsInclude($include=array())
	{
		$holdings = array();
		foreach ($include as $cur){
			if(array_key_exists($cur, $this->holdings)){
				$holdings[$cur] = $this->holdings[$cur];
			}
		}
		return $holdings;
	}
	
	public function getHoldingsExclude($exclude=array())
	{
		$holdings = array();
		foreach ($this->holdings as $cur=>$val){
			if(!in_array($cur, $exclude)){
				$holdings[$cur] = $val;
			}
		}
		return $holdings;
	}
	
	public function getExchangesForCur($cur){
		return $this->holdings_per_exchange[$cur];
	}
	
	public function getTransaction()
	{
		return $this->transaction;
	}
	
	public function getInvestment()
	{
		return $this->investment;
	}
	
	public function getValueBTC()
	{
		return $this->total_btc;
	}
	
	public function getValueEUR()
	{
		return $this->total_eur;
	}
	
	public function getValueUSD()
	{
		return $this->total_usd;
	}
}

?>