<?php 
namespace ofxtocsv;

require_once('FinalParser.php');

$ofxtocsv = new Parser();
//$fileName = 'test_ready_Input_Files/2018-01 0BTL TRANSACTIONS 7787.QBO';
$fileName = $argv[1];
echo $fileName;
$ofx = $ofxtocsv->loadFromFile($fileName);
//print_r($ofx->mainAccount);
//print_r($ofx->mainAccount->statement->transactions[0]);

$data = array(
    array('Row #','Date','Payee','Amount','In','Out','Balance','Currency','Memo','Check #','Unique Transaction ID','Record Type','Investment Action','Security ID','Security Name','Ticker','Price','Quantity of Shares','Commission','Trade Date','Sell Type','Buy Type','Initiated','Settle Date','Account #','Account Type','Bank ID','Branch ID','FI_ORG','FI_ID','Intu_BID','FileName')
);
$count = 1;
$tempBal = $ofx->mainAccount->balance;
$lastBal = 0;
foreach($ofx->mainAccount->statement->transactions as $transaction){
    $in = null;
    $out = null;
    if($transaction->amount < 0){
        $out = abs($transaction->amount);
    }else{
        $in = $transaction->amount;
    }
    if($lastBal < 0){
        $tempBal = $tempBal+abs($lastBal);
    }else if($lastBal > 0){
        $tempBal = $tempBal-$lastBal;
    }
    array_push($data,array(
        $count++,
        date_format($transaction->date,"m/d/Y"),
        $transaction->name,
        $transaction->amount,
        $in,
        $out,
        $tempBal,
        $ofx->mainAccount->statement->currency,
        '',
        '',
        $transaction->uniqueId,
        $transaction->type,
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        date_format($transaction->date,"m/d/Y"),
        '',
        '',
        date_format($transaction->userInitiatedDate,"m/d/Y"),
        date_format($transaction->date,"m/d/Y"),
        $ofx->mainAccount->accountNumber,
        $ofx->mainAccount->accountType,
        '',
        '',
        '',
        '',
        '',       
        basename($fileName) 
    ));

    $lastBal = $transaction->amount;
}
$file = fopen(substr($fileName,0,strrpos($fileName,'.')).'.csv', 'w');
if($file){
    // save each row of the data
    foreach ($data as $row)
    {
    fputcsv($file, $row);
    }
    
    // Close the file
    fclose($file);
}else{
    echo "access denied...";
}
?>