<?php
require_once 'BankingReconciliation.php';

// To generate the starting and ending dates for August 2019

$startDate = "2019-08-01";

$calendarMonthStartDate = new DateTime($startDate);
$calendarMonthEndDate = (new DateTime($startDate))->modify('last day of this month');


echo "Starting Calendar Date: \t" .   $calendarMonthStartDate->format('Y-m-d') . "\n";
echo "Ending Calendar Date: \t\t" . $calendarMonthEndDate->format('Y-m-d') . "\n\n";

$bankcardStartingDate = BankingReconciliation::calculateEarliestProcessingDateWithDepositDateGreaterThanOrEqualTo($calendarMonthStartDate, BankingReconciliation::BANKCARD);
$bankcardEndingDate   = BankingReconciliation::calculateLatestProcessingDateWithDepositDateLessThanOrEqualTo($calendarMonthEndDate, BankingReconciliation::BANKCARD);

echo "Starting Bankcard Date: \t" .   $bankcardStartingDate->format('Y-m-d') . "\n";
echo "Ending Bankcard Date: \t\t" . $bankcardEndingDate->format('Y-m-d') . "\n\n";

$amexStartingDate = BankingReconciliation::calculateEarliestProcessingDateWithDepositDateGreaterThanOrEqualTo($calendarMonthStartDate, BankingReconciliation::AMEX);
$amexEndingDate   = BankingReconciliation::calculateLatestProcessingDateWithDepositDateLessThanOrEqualTo($calendarMonthEndDate, BankingReconciliation::AMEX);

echo "Starting Amex Date: \t\t" .   $amexStartingDate->format('Y-m-d') . "\n";
echo "Ending Amex Date: \t\t" . $amexEndingDate->format('Y-m-d') . "\n\n";

?>