<?php
require_once 'BankingReconciliation.php';


$dt = new DateTime("2019-08-01");

// For Bankcard payments/refunds AND related fees:
// Should calculate at processing time
$bankcardEffectiveDate = BankingReconciliation::calculateEffectiveDate($dt, BankingReconciliation::BANKCARD);

// For Amex payments/refunds AND related fees:
// Should calculate at processing time
$amexEffectiveDate = BankingReconciliation::calculateEffectiveDate($dt, BankingReconciliation::AMEX);

// For Payouts AND related fees:
// Should calculate at NACHA file generation time
$payoutEffectiveDate = BankingReconciliation::calculateEffectiveDate($dt, BankingReconciliation::ACH);

// For adjustments:
// Should calculate at creation time 
$adjustmentEffectiveDate = BankingReconciliation::calculateEffectiveDate($dt, BankingReconciliation::ACH); // essentially the same schedule as ACH which is next banking day (including today) 

// For Chargebacks:
// TODO!

?>