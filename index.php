<?php 

include('config.inc.php');

?>
<!DOCTYPE html>
<html>
  <head>
    <title>CryptoLab</title>
    <link rel="stylesheet" href="style.css" />
    <script async src="script.js"></script>
  </head>
  <body>
  <div>
  hide/show 
  all transactions:<input type="checkbox" id="toggletransactions" onclick="toggleClassDisplay('toggletransactions','holding_transaction');">  
  debug:<input type="checkbox" id="toggledebug" onclick="toggleClassDisplay('toggledebug','timestamp','inline');toggleClassDisplay('toggledebug','debug_container');">
  </div>
<?php

//create CryptoLab
$cryptolab = new CryptoLab();
$cryptolab->load_data('data/example.csv');
$investment_cur = 'EUR';
$cryptolab->track_investment($investment_cur);

//print yearly holdings
$dates = array();
for ($year=2016;$year<=date('Y');$year++){
	$dates[] = $year.'-01-01';
}
//print holdings today
$dates[] = date('Y-m-d H:i');

//process all data
$cryptolab->process($dates);


//show holdings
$html = '<div class="holdings_container">';
$fiat = array('EUR','USD');//non crypto currencies
$rowcount=0;
foreach ($cryptolab->getHoldings() as $holding)
{	
	$holdings_crypto = $holding->getHoldingsExclude($fiat);	
	$holdings_fiat = $holding->getHoldingsInclude($fiat);
	
	$cur_holdings_crypto  = array_keys($holdings_crypto);
	$cur_holdings_fiat  = array_keys($holdings_fiat);
	
	$rowcount_crypto = count($holdings_crypto);
	$rowcount_fiat = count($holdings_fiat);
	
	$class = ($holding->getTransaction()!==false)?'holding_transaction':'holding';

	$html .='<div class="'.$class.'">';
	$style='';
	if($holding->getTransaction()!==false)
	{
		$html .='<div onclick="toggleElementDisplay(\'holding_container_'.$rowcount.'\');">';
		$html .= print_arr($holding->getTransaction(),'transaction');
		$html .='</div>';
		$style = 'style="display:none;"';
	}
	$html .='<div id="holding_container_'.$rowcount.'" '.$style.'>';
		$html .='<div class="holding_header"><span class="title">Holdings '.$holding->getDate().'</span><span class="timestamp">(timestamp='.strtotime($holding->getDate()).')</span></div>';
		$html .='<div class="holding_coins">';
		$html .='<table>';
		$html .='<tr>
						<th colspan="3">Crypto</th>
						<th colspan="3">Fiat</th>
					  </tr>';
		for($i=0;$i<max($rowcount_crypto,$rowcount_fiat);$i++){
			$html .='<tr>';
			if($i<$rowcount_crypto){
				$html .='<td class="holding_cur '.$cur_holdings_crypto[$i].'">'.$cur_holdings_crypto[$i].'&nbsp;</td>
							<td>'.$holdings_crypto[$cur_holdings_crypto[$i]].'&nbsp;</td>
							<td>'.print_arr($holding->getExchangesForCur($cur_holdings_crypto[$i]),'holdings_per_exchange').'&nbsp;</td>';
			}
			else{
				$html .='<td colspan="3"></td>';
			}
			if($i<$rowcount_fiat){
				$html .='<td class="holding_cur '.$cur_holdings_fiat[$i].'">'.$cur_holdings_fiat[$i].'&nbsp;</td>
							<td>'.$holdings_fiat[$cur_holdings_fiat[$i]].'&nbsp;</td>
							<td>'.print_arr($holding->getExchangesForCur($cur_holdings_fiat[$i]),'holdings_per_exchange').'&nbsp;</td>
						  </tr>';
			}
			else{
				$html .='<td colspan="3"></td>';
			}
		}
		$html .='</table>';
		$html .='</div>';	
		$html .='<div class="holding_investment">Investment: '.$holding->getInvestment().' '.$investment_cur.'</div>';
		
		$tax = (($holding->getValueEUR()-30000)*0.012);
		$tax_estimate = ($tax<0)? '':'(tax estimate='.$tax.')';
		
		$html .='<div class="holding_value">Total value: '.$holding->getValueBTC().' btc, '.$holding->getValueUSD().' usd, '.$holding->getValueEUR().' eur'.' '.$tax_estimate.'</div>';
		$html .='</div>';
	$html .='</div>';
	$rowcount++;
}

$html .='</div>';

//first print errors if any
$errors = $cryptolab->getErrors();
if(count($errors)>0){
	echo '<div class="error_container">';
	echo 'ERRORS FOUND:<br/>';
	foreach ($errors as $error)
	{
		echo '<div class="error">'.$error.'</div>';
	}
	echo '</div>';
}

echo $html;

//show max investment
$max_investment = $cryptolab->getMaxInvestment();
echo '<div class="max_investment_container">';
echo 'Max investment: '.$max_investment['investment'].' '.$investment_cur.' on '.$max_investment['transaction']['Date'];
echo '</div>';



//DEBUG INCOIMG AND OUTGOING TRANSACTIONS

$html = '<div class="debug_container" style="display:none;">';
$html .= 'Debug incoming/outgoing transactions:<br/>';

foreach ($cryptolab->getCurrencies() as $cur)
{
	$data = $cryptolab->getTransactionInputOutputsForCur($cur);
	if($data!==false)
	{
		$html .= '<div class="ingoing_outgoing_container">';
		$html .= '<div class="ingoing_outgoing_cur">'.$cur.'</div>';
		foreach ($data['transactions'] as $transaction)
		{
			$html .= print_arr($transaction,'transaction');
		}
		$html .= 'Total Deposit='.$data['deposit'].' Withdrawal='.$data['withdrawal'].' Profit='.$data['profit'].'<br/>';
		$html .= '</div>';
	}
	
}

$html .= '</div>';

echo $html;
?>
  </body>
</html>
<?php 

//HELPER FUNCTION

function print_arr($arr, $classname='',$exclude=array(''))
{
	$output = array();
	foreach ($arr as $k=>$v)
	{
		$show = true;
		foreach ($exclude as $ex)
		{
			if($v==$ex){
				$show = false;
				break;
			}
		}
		if($show)
		{
			$output[] = '<span class="item">'.$k.'='.$v.'</span>';
		}
	}
	$class = ($classname!='')?' class="'.$classname.'"':'';
	$html = '<div'.$class.'>';
	$html .= implode(', ', $output);
	$html .= '</div>';

	return $html;
}

?>