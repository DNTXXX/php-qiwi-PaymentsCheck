<?php

$ch = curl_init();  

$accesstoken = ''; // qiwi token
$qiwi_url = 'https://edge.qiwi.com/payment-history/v2/persons/***********/payments?rows=1&operation=IN';

$db_server = '';
$db_user = '';
$db_pw = '';
$mysqli = new mysqli($db_server, $db_user, $db_pw , '');

$headr = array();
$headr[] = 'Accept: application/json';
$headr[] = 'Content-type: application/json';
$headr[] = 'Authorization: Bearer '.$accesstoken;

curl_setopt($ch, CURLOPT_URL, $qiwi_url);  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);  

$output = curl_exec($ch);  
curl_close($ch);
$result = json_decode($output);

if ($output === false)
{
    print_r('Curl error: ' . curl_error($ch));
}

$mysql_query = $mysqli->query("SELECT * FROM `qiwi_payments` ORDER BY ID DESC LIMIT 1");
$row = mysqli_fetch_row($mysql_query);

if ($result->data[0]->txnId != $row[1])
{
	$mysqli->query("INSERT INTO `danate_vazgta_main`.`qiwi_payments` (`transaction_id`, `date`, `qiwi_num`, `comment`, `summ`, `status`) VALUES ('".$result->data[0]->txnId."', '".$result->data[0]->date."', '".$result->data[0]->account."', '".$result->data[0]->comment."', '".$result->data[0]->sum->amount."', '".$result->data[0]->statusText."')");

	file_put_contents('payments/history.php', '<br>Платеж от ' . $result->data[0]->date . ' через QIWI на сумму ' . $result->data[0]->sum->amount . ' логин ' . $result->data[0]->account . PHP_EOL, FILE_APPEND);
}

?>