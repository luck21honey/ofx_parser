<?php

namespace ofxtocsv;

error_reporting(0);

require_once('FinalParser.php');

$dir = 'ofxtmp';

if (isset($_POST['delete_all']) && $_POST['delete_all'] != '') {

    function deleteAll($str)
    {
        //It it's a file.
        if (is_file($str)) {
            //Attempt to delete it.
            return unlink($str);
        }
        //If it's a directory.
        elseif (is_dir($str)) {
            //Get a list of the files in this directory.
            $scan = glob(rtrim($str, '/') . '/*');
            //Loop through the list of files.
            foreach ($scan as $index => $path) {
                //Call our recursive function.
                deleteAll($path);
            }
            //Remove the directory itself.
            return @rmdir($str);
        }
    }

    //call our function
    deleteAll($dir);
    if (!file_exists($dir) || !is_dir($dir)) {
        mkdir($dir);
    }

    header('Location: ./');
    exit;
}


if (!file_exists($dir) || !is_dir($dir)) {
    mkdir($dir);
}

if (isset($_POST['location']) && $_POST['location'] != '') {
    $location = $_POST['location'];
    if (!file_exists($dir . '/' . $location)) {
        mkdir($dir . '/' . $location);
    }
    $location = $dir . '/' . $location . '/';
} else {
    $location = $dir . '/';
}



foreach ($_FILES['files']['name'] as $key => $value) :

    $ofxtocsv = new Parser();
    //$fileName = '2017-11 0MRC TRANSACTIONS 8480.QBO';
    // $fileName = $argv[1];
    $fileName = $value;
    $fileContent = $_FILES['files']['tmp_name'][$key];
    //echo $fileName; 
    //print_r($ofx->mainAccount);
    //print_r($ofx->mainAccount->statement->transactions[0]);

    $data = array(
        array('Row #', 'Date', 'Payee', 'Amount', 'In', 'Out', 'Balance', 'Currency', 'Memo', 'Check #', 'Unique Transaction ID', 'Record Type', 'Investment Action', 'Security ID', 'Security Name', 'Ticker', 'Price', 'Quantity of Shares', 'Commission', 'Trade Date', 'Sell Type', 'Buy Type', 'Initiated', 'Settle Date', 'Account #', 'Account Type', 'Bank ID', 'Branch ID', 'FI_ORG', 'FI_ID', 'Intu_BID', 'FileName')
    );

    // echo $fileName.DIRECTORY_SEPARATOR.$value."\n";
    $ofx = $ofxtocsv->loadFromFile($fileContent);

    $count = 1;
    $tempBal = $ofx->mainAccount->balance;
    $lastBal = 0;
    foreach ($ofx->mainAccount->statement->transactions as $transaction) {
        $in = null;
        $out = null;
        if ($transaction->amount < 0) {
            $out = abs($transaction->amount);
        } else {
            $in = $transaction->amount;
        }
        if ($lastBal < 0) {
            $tempBal = $tempBal + abs($lastBal);
        } else if ($lastBal > 0) {
            $tempBal = $tempBal - $lastBal;
        }
        // var_dump($transaction->checkNumber);
        // exit;
        $arr = array(
            $count++,
            date_format($transaction->date, "m/d/Y"),
            trim($transaction->name) . ' & ' . trim($transaction->memo),
            $transaction->amount,
            $in,
            $out,
            trim($tempBal),
            trim($ofx->mainAccount->statement->currency),
            '',
            trim($transaction->checkNumber),
            trim($transaction->uniqueId),
            trim($transaction->type),
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            date_format($transaction->date, "m/d/Y"),
            '',
            '',
            date_format($transaction->userInitiatedDate, "m/d/Y"),
            date_format($transaction->date, "m/d/Y"),
            trim($ofx->mainAccount->accountNumber),
            trim($ofx->mainAccount->accountType),
            '',
            '',
            '',
            '',
            '',
            basename($fileName . DIRECTORY_SEPARATOR . $value)
        );

        array_push($data, $arr);

        $lastBal = $transaction->amount;
    }
    $filename = substr($fileName, 0, strrpos($fileName, '.')) . '.csv';
    $file = fopen($location . $filename, 'w');
    if ($file) {
        // save each row of the data
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
        // Close the file
        fclose($file);
    } else {
        echo "access denied...";
        var_dump("access denied");
        exit;
    }

endforeach;

header('Location: ofxtmp');
