<?php

namespace ofxtocsv;
foreach (glob("Entities/*.php") as $filename)
{
    include $filename;
}


use SimpleXMLElement;
use ofxtocsv\Entities\BankAccount;
use ofxtocsv\Entities\Statement;
use ofxtocsv\Entities\Transaction;


class OfxParser{

    public $mainAccount;

    public function __construct(SimpleXMLElement $xmlData)
    {
        if (isset($xmlData->BANKMSGSRSV1)) {
            $this->mainAccount = $this->getBankAccounts($xmlData)[0];
        } elseif (isset($xmlData->CREDITCARDMSGSRSV1)) {
            $this->mainAccount = $this->getCreditCardAccounts($xmlData)[0];
        }
    }

    private function getCreditCardAccounts(SimpleXMLElement $xmlData)
    {
        $tempAccounts = [];

        foreach ($xmlData->CREDITCARDMSGSRSV1->CCSTMTTRNRS as $statements) {
            $tempAccounts[] = $this->getCreditAccount($statements);
        }
        return $tempAccounts;
    }

    private function getBankAccounts(SimpleXMLElement $xml)
    {
        $bankAccounts = [];
        foreach ($xml->BANKMSGSRSV1->STMTTRNRS as $accountStatement) {
            foreach ($accountStatement->STMTRS as $statementResponse) {
                $bankAccounts[] = $this->getBankAccount($accountStatement->TRNUID, $statementResponse);
            }
        }
        return $bankAccounts;
    }

    private function getBankAccount($transactionUid, SimpleXMLElement $statementResponse)
    {
        $bankAccount = new BankAccount();
        $bankAccount->transactionUid = $transactionUid;
        $bankAccount->agencyNumber = $statementResponse->BANKACCTFROM->BRANCHID;
        $bankAccount->accountNumber = $statementResponse->BANKACCTFROM->ACCTID;
        $bankAccount->routingNumber = $statementResponse->BANKACCTFROM->BANKID;
        $bankAccount->accountType = $statementResponse->BANKACCTFROM->ACCTTYPE;
        $bankAccount->balance = $statementResponse->LEDGERBAL->BALAMT;
        $bankAccount->balanceDate = $this->getDateTime(
            $statementResponse->LEDGERBAL->DTASOF,
            true
        );

        $bankAccount->statement = new Statement();
        $bankAccount->statement->currency = $statementResponse->CURDEF;

        $bankAccount->statement->startDate = $this->getDateTime(
            $statementResponse->BANKTRANLIST->DTSTART
        );

        $bankAccount->statement->endDate = $this->getDateTime(
            $statementResponse->BANKTRANLIST->DTEND
        );


        if($statementResponse->BANKTRANLIST->STMTTRN != null){
            $bankAccount->statement->transactions = $this->getTransactions(
                $statementResponse->BANKTRANLIST->STMTTRN
            );
        }else{
            $bankAccount->statement->transactions = array();
        }

        return $bankAccount;
    }

    private function getCreditAccount(SimpleXMLElement $xmlData)
    {
        $nodeName = 'CCACCTFROM';
        if (!isset($xmlData->CCSTMTRS->$nodeName)) {
            $nodeName = 'BANKACCTFROM';
        }

        $creditAccount = new BankAccount();
        $creditAccount->transactionUid = $xmlData->TRNUID;
        $creditAccount->agencyNumber = $xmlData->CCSTMTRS->$nodeName->BRANCHID;
        $creditAccount->accountNumber = $xmlData->CCSTMTRS->$nodeName->ACCTID;
        $creditAccount->routingNumber = $xmlData->CCSTMTRS->$nodeName->BANKID;
        $creditAccount->accountType = $xmlData->CCSTMTRS->$nodeName->ACCTTYPE;
        $creditAccount->balance = $this->getAmount($xmlData->CCSTMTRS->LEDGERBAL->BALAMT);
        $creditAccount->balanceDate = $this->getDateTime($xmlData->CCSTMTRS->LEDGERBAL->DTASOF, true);

        $creditAccount->statement = new Statement();
        $creditAccount->statement->currency = $xmlData->CCSTMTRS->CURDEF;
        $creditAccount->statement->startDate = $this->getDateTime($xmlData->CCSTMTRS->BANKTRANLIST->DTSTART);
        $creditAccount->statement->endDate = $this->getDateTime($xmlData->CCSTMTRS->BANKTRANLIST->DTEND);
        if($xmlData->CCSTMTRS->BANKTRANLIST->STMTTRN != null){
            $creditAccount->statement->transactions = $this->getTransactions($xmlData->CCSTMTRS->BANKTRANLIST->STMTTRN);
        }else{
            $creditAccount->statement->transactions = array();
        }

        return $creditAccount;
    }

    private function getTransactions(SimpleXMLElement $transactions)
    {
        $return = [];

        foreach ($transactions as $transactionData) {
            $transaction = new Transaction();
            $transaction->type = (string)$transactionData->TRNTYPE;
            $transaction->date = $this->getDateTime($transactionData->DTPOSTED);
            if ('' !== (string)$transactionData->DTUSER) {
                $transaction->userInitiatedDate = $this->getDateTime($transactionData->DTUSER);
            }else{
                $transaction->userInitiatedDate = $this->getDateTime($transactionData->DTPOSTED);
            }
            $transaction->amount = $this->getAmount($transactionData->TRNAMT);
            $transaction->uniqueId = (string)$transactionData->FITID;
            $transaction->name = (string)$transactionData->NAME;
            $transaction->memo = (string)$transactionData->MEMO;
            $transaction->sic = $transactionData->SIC;
            $transaction->checkNumber = $transactionData->CHECKNUM;
            $return[] = $transaction;
        }

        return $return;
    }

    private function getAmount($amountString)
    {
        //(UK/US): 000.00 or 0,000.00
        if (preg_match('/^(-|\+)?([\d,]+)(\.?)([\d]{2})$/', $amountString) === 1) {
            return (float)preg_replace(
                ['/([,]+)/', '/\.?([\d]{2})$/'],
                ['', '.$1'],
                $amountString
            );
        }

        // European : 000,00 or 0.000,00
        if (preg_match('/^(-|\+)?([\d\.]+,?[\d]{2})$/', $amountString) === 1) {
            return (float)preg_replace(
                ['/([\.]+)/', '/,?([\d]{2})$/'],
                ['', '.$1'],
                $amountString
            );
        }

        return (float)$amountString;
    }

    private function getDateTime($dateString, $ignoreErrors = false)
    {
        if((!isset($dateString) || trim($dateString) === '')) return null;
        
        $regex = '/'
            . "(\d{4})(\d{2})(\d{2})?"     
            . "(?:(\d{2})(\d{2})(\d{2}))?" 
            . "(?:\.(\d{3}))?"             
            . "(?:\[(-?\d+)\:(\w{3}\]))?" 
            . '/';

        if (preg_match($regex, $dateString, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];
            $hour = isset($matches[4]) ? $matches[4] : 0;
            $min = isset($matches[5]) ? $matches[5] : 0;
            $sec = isset($matches[6]) ? $matches[6] : 0;

            $format = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $min . ':' . $sec;

            try {
                return new \DateTime($format);
            } catch (\Exception $e) {
                if ($ignoreErrors) {
                    return null;
                }

                throw $e;
            }
        }

        throw new \RuntimeException('Failed to initialize DateTime for string: ' . $dateString);
    }

}
?>