<?php 

require_once('class.holding.php');

class CryptoLab
{
	var $transactions = array();
	var $holdings = array();
	var $errors = array();
	var $max_investment = array();
		
	var $investment_cur = false;

	
	// data structure needs to contain:
	// "Type","Buy","BuyCur","Sell","SellCur","Fee","FeeCur","Exchange","Date"
	// $csv = file name
	public function load_data($csv)
	{
		$transactions = $this->csv2array($csv);
	
		//sort data
		usort($transactions, function($a1, $a2) {
			$v1 = strtotime($a1['Date']);
			$v2 = strtotime($a2['Date']);
			return $v1 - $v2; // $v2 - $v1 to reverse direction
		});
	
		$this->transactions = $transactions;
	}
	
	public function track_investment($cur)
	{
		$this->investment_cur = strtoupper($cur);
	}
		
	public function process($dates=array())
	{
		//reset holdings array	
		$this->holdings = array();	
		$this->errors = array();
		$this->max_investment = array();
		
		
		$current_holdings = array();
		$current_holdings_per_exchange = array();
		$investment = 0;
		
		foreach ($this->transactions as $transaction)
		{		
			//print holdings of given non transactional dates
			$time = strtotime($transaction['Date']);
			foreach ($dates as $date)
			{
				if(strtotime($date)<$time)
				{
					$this->holdings[]= new Holding($date,$current_holdings,$current_holdings_per_exchange,$investment);
					if (($key = array_search($date, $dates)) !== false) {
					    unset($dates[$key]);
					}
				}
			}
			
			//debug some exchange
			//if($transaction['Exchange']!='Kraken'){
			//continue;
			//}
			
			//instantiate currency in datastructure
			$this->add_currency($current_holdings,$current_holdings_per_exchange,$transaction['BuyCur'],$transaction['Exchange']);
			$this->add_currency($current_holdings,$current_holdings_per_exchange,$transaction['SellCur'],$transaction['Exchange']);
			$this->add_currency($current_holdings,$current_holdings_per_exchange,$transaction['FeeCur'],$transaction['Exchange']);
		
			switch ($transaction['Type']){
				case "Deposit":
					$this->add($current_holdings[$transaction['BuyCur']],$transaction['Buy'],$transaction['BuyCur']);
					$this->add($current_holdings_per_exchange[$transaction['BuyCur']][$transaction['Exchange']],$transaction['Buy'],$transaction['BuyCur']);
					break;
				case "Withdrawal":
					$this->subtract($current_holdings[$transaction['SellCur']],$transaction['Sell'],$transaction['SellCur']);
					$this->subtract($current_holdings_per_exchange[$transaction['SellCur']][$transaction['Exchange']],$transaction['Sell'],$transaction['SellCur']);
					break;
				case "Trade":
					//fee is already included in Sell
					$this->add($current_holdings[$transaction['BuyCur']],$transaction['Buy'],$transaction['BuyCur']);
					$this->add($current_holdings_per_exchange[$transaction['BuyCur']][$transaction['Exchange']],$transaction['Buy'],$transaction['BuyCur']);
					$this->subtract($current_holdings[$transaction['SellCur']],$transaction['Sell'],$transaction['SellCur']);
					$this->subtract($current_holdings_per_exchange[$transaction['SellCur']][$transaction['Exchange']],$transaction['Sell'],$transaction['SellCur']);	
					break;
				case "Gift":
					$this->subtract($current_holdings[$transaction['SellCur']],$transaction['Sell'],$transaction['SellCur']);
					$this->subtract($current_holdings_per_exchange[$transaction['SellCur']][$transaction['Exchange']],$transaction['Sell'],$transaction['SellCur']);
					break;
				case "Spend":
					$this->subtract($current_holdings[$transaction['SellCur']],$transaction['Sell'],$transaction['SellCur']);
					$this->subtract($current_holdings_per_exchange[$transaction['SellCur']][$transaction['Exchange']],$transaction['Sell'],$transaction['SellCur']);
					break;
				default:
					$this->errors[] = 'Unknown Type in transaction with date '.$transaction['Date'];
			}
			
			//error check on negative values
			if(array_key_exists($transaction['SellCur'],$current_holdings))
			{
				if($current_holdings[$transaction['SellCur']]<0){
					$this->errors[] = 'Negative value '.$current_holdings[$transaction['SellCur']].' '.$transaction['SellCur'].' for transaction with date '.$transaction['Date'];
				}
				if($current_holdings_per_exchange[$transaction['SellCur']][$transaction['Exchange']]<0){
					$this->errors[] = 'Exchange '.$transaction['Exchange'].' has negative value '.$current_holdings_per_exchange[$transaction['SellCur']][$transaction['Exchange']].' '.$transaction['SellCur'].' for transaction with date '.$transaction['Date'];
				}
			}
			
			//investment info
			if($this->investment_cur!==false)
			{
				if($transaction['Type']=='Deposit' && $transaction['BuyCur']==$this->investment_cur){
					$investment += $transaction['Buy'];
				}
				elseif($transaction['Type']=='Withdrawal' && $transaction['SellCur']==$this->investment_cur){
					$investment -= $transaction['Sell'];
				}
				if(!isset($this->max_investment['investment'])){
					$this->max_investment['investment'] = 0;
				}
				if($this->max_investment['investment']<$investment){
					$this->max_investment['investment'] = $investment;
					$this->max_investment['transaction'] = $transaction;
				}
			}

			$this->holdings[]= new Holding($transaction['Date'],$current_holdings,$current_holdings_per_exchange,$investment,$transaction);
		}
		
		//print holdings of remaining given non transactional dates
		foreach ($dates as $date)
		{
			$this->holdings[]= new Holding($date,$current_holdings,$current_holdings_per_exchange,$investment);
		}
	}
	
	private function add(&$holding,$val,$cur)
	{
		$holding += floatval($val);
		$this->roundtozero($holding,$cur);
	}
	
	private function subtract(&$holding,$val,$cur)
	{
		$holding -= floatval($val);
		$this->roundtozero($holding,$cur);
	}
	
	private function roundtozero(&$holding,$cur)
	{
		if(defined($cur)){
			$precision = constant($cur);
			
			$val = abs($holding);
			$sn =explode('E-',$val);
			if(count($sn)>1){
				//number with scientific notation
				$val = str_replace('.','',$sn[0]);
				for ($i=$sn[1];$i>0;$i--){
					if($i==1)
						$val = '.'.$val;
					$val = '0'.$val;
				}
			}
			$val = substr($val,0,$precision+2);
			
			$zero = '0.'.str_repeat('0',$precision);
			
			if($val==$zero){
				$holding = 0;
			}
		}
	}
	
	public function getErrors()
	{
		return $this->errors;
	}
	
	public function getHoldings()
	{
		return $this->holdings;
	}
	
	public function getHoldingByDate($date)
	{
		foreach ($this->holdings as $holding)
		{
			if($holding->getDate()==$date){
				return $holding;
			}
		}
		return false;
	}
	
	public function getMaxInvestment(){
		return $this->max_investment;
	}
	
	public function getCurrencies()
	{
		$currencies = array();
		foreach ($this->transactions as $transaction)
		{
			if(!in_array($transaction['BuyCur'],$currencies))
			{
				$currencies[]=$transaction['BuyCur'];
			}
			if(!in_array($transaction['SellCur'],$currencies))
			{
				$currencies[]=$transaction['SellCur'];
			}
			if(!in_array($transaction['FeeCur'],$currencies))
			{
				$currencies[]=$transaction['FeeCur'];
			}
		}
		return $currencies;
	}
	
	public function getTransactionInputOutputsForCur($cur)
	{
		$transactions = array();
		$deposit = 0;
		$withdrawal = 0;
		
		$remember_row = false;
		foreach ($this->transactions as $row){
			if($row['Type']=='Deposit' && $row['BuyCur']==$cur){
				if($remember_row!==false && ((string)floatval($row['Buy'])==(string)(floatval($remember_row['Sell'])-floatval($remember_row['Fee'])))){
					//Withdrawal is deposited so it cancelles out.
					$remember_row = false;
					continue;
				}
				else{
					if($remember_row!==false){
						$transactions[] = $remember_row;
						$withdrawal+=floatval($remember_row['Sell']);
						$remember_row = false;
					}
					$transactions[] = $row;
					$deposit+=floatval($row['Buy']);
				}
			}
			elseif($row['Type']=='Withdrawal' && $row['SellCur']==$cur){
				if($remember_row!==false){
					$transactions[] = $remember_row;
					$withdrawal+=floatval($remember_row['Sell']);
					$remember_row = false;
				}
				$remember_row = $row;
			}
		}
		if($remember_row!==false){
			$transactions[] = $remember_row;
			$withdrawal+=floatval($remember_row['Sell']);
			$remember_row = false;
		}
		
		if(count($transactions)>0)
		{
			$output = array();
			$output['transactions'] = $transactions;
			$output['deposit'] = $deposit;
			$output['withdrawal'] = $withdrawal;
			$output['profit'] = ($deposit-$withdrawal);
			return $output;
		}
		
		return false;
	}
	
	//add currency to holdings if it does not exist already
	private function add_currency(&$current_holdings,&$current_holdings_per_exchange,$cur,$exchange)
	{
		if($cur!='')
		{
			if(!array_key_exists($cur, $current_holdings)){
				$current_holdings[$cur] = 0;
				$current_holdings_per_exchange[$cur] = array();
			}
			if(!array_key_exists($exchange, $current_holdings_per_exchange[$cur])){
				$current_holdings_per_exchange[$cur][$exchange] = 0;
			}
		}
	}

	static function csv2array($csv)
	{
		$arr = array_map('str_getcsv', file($csv));
		array_walk($arr, function(&$a) use ($arr) {
			$a = array_combine($arr[0], $a);
		});
		array_shift($arr); # remove column header
		
		return $arr;
	}
}

?>