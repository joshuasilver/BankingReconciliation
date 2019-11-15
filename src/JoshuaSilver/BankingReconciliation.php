<?php
namespace JoshuaSilver;

/**
 *
 * This code can be used when generating bank reconciliation reports for credit card processing.
 * Both Amex and Bankcard (Visa/Mastercard/Discover) card types are supported
 *
 * @author  Joshua Silver <joshua@joshuasilver.net>
 *
 */

class BankingReconciliation {

	public const AMEX = 'amex';
	public const BANKCARD = 'bankcard'; // Visa, MasterCard, Discover
	public const ACH = 'ach';

	public static $bankHolidays = array( // https://www.frbservices.org/about/holiday-schedules/index.html
		"2017-01-02",
		"2017-01-16",
		"2017-01-20", // conflicting reports of whether this was a one-time holiday or not.
		"2017-02-20",
		"2017-05-29",
		"2017-07-04",
		"2017-09-04",
		"2017-10-09",
		"2017-11-11",
		"2017-11-23",
		"2017-12-25",

		"2018-01-01",
		"2018-01-15",
		"2018-02-19",
		"2018-05-28",
		"2018-07-04",
		"2018-09-03",
		"2018-10-08",
		"2018-11-12",
		"2018-11-22",
		"2018-12-25",

		"2019-01-01",
		"2019-01-21",
		"2019-02-18",
		"2019-05-27",
		"2019-07-04",
		"2019-09-02",
		"2019-10-14",
		"2019-11-11",
		"2019-11-28",
		"2019-12-25",

		"2020-01-01",
		"2020-01-20",
		"2020-02-17",
		"2020-05-25",
		"2020-07-04",
		"2020-09-07",
		"2020-10-12",
		"2020-11-11",
		"2020-11-26",
		"2020-12-25",

		"2021-01-01",
		"2021-01-18",
		"2021-02-15",
		"2021-05-31",
		"2021-07-04",
		"2021-09-06",
		"2021-10-11",
		"2021-11-11",
		"2021-11-25",
		"2021-12-25",

		"2022-01-01",
		"2022-01-17",
		"2022-02-21",
		"2022-05-30",
		"2022-07-04",
		"2022-09-05",
		"2022-10-10",
		"2022-11-11",
		"2022-11-24",
		"2022-12-25",

		"2023-01-01",
		"2023-01-16",
		"2023-02-20",
		"2023-05-29",
		"2023-07-04",
		"2023-09-04",
		"2023-10-09",
		"2023-11-11",
		"2023-11-23",
		"2023-12-25",
	);

	public static function isBankingDay($dt) {

		$earliestSupportedDate = new DateTime('2017-01-01');
		$latestSupportedDate = new DateTime('2023-12-31');

		if (!($dt instanceof DateTime)) {
			throw new BankingReconciliationException("Must pass in DateTime, passed in: " . getType($dt));
		}

		if ($dt < $earliestSupportedDate) {
			throw new BankingReconciliationException("Dates before " . $earliestSupportedDate->format('Y-m-d') . " are not supported.  Passed in: " . $dt->format('Y-m-d'));
		}

		if ($dt >= $latestSupportedDate) {
			throw new BankingReconciliationException("Dates after " . $latestSupportedDate->format('Y-m-d') . " are not supported.  Passed in: " . $dt->format('Y-m-d'));
		}

		if (in_array($dt->format('w'), array('0','6'))) { // Sun or Sat.  Not a banking day
			return false;
		}

		if (in_array($dt->format('Y-m-d'), self::$bankHolidays)) { // Is a banking holiday
			return false;
		}

		return true;
	}

	public static function calculateEarliestProcessingDateWithDepositDateGreaterThanOrEqualTo($dt, $paymentType) {
		$dt = clone $dt; // prevent parameter object from being modified

		switch ($paymentType) {
			case self::BANKCARD:
				if (!BankingReconciliation::isBankingDay($dt)){  // if NOT a banking day, recurse after adding 1  day
					return self::calculateEarliestProcessingDateWithDepositDateGreaterThanOrEqualTo($dt->add(new DateInterval('P1D')), $paymentType);
				} else {
					 // Subtract 1 banking day and then subtract 1 calendar day
					return self::subtractBankingDays($dt, 1)->sub(new DateInterval('P1D'));
				}
				break;
			case self::AMEX:
				if (!BankingReconciliation::isBankingDay($dt)){  // if NOT a banking day, recurse subtracting 1  day
					return self::calculateEarliestProcessingDateWithDepositDateGreaterThanOrEqualTo($dt->sub(new DateInterval('P1D')), $paymentType);
				} else {
					 // Subtract 1 banking day and then subtract 2 calendar day
					return self::subtractBankingDays($dt, 1)->sub(new DateInterval('P2D'));
				}
				break;
			default:
				throw new BankingReconciliationException("Invalid paymentType. Please pass in const AMEX or BANKCARD");
		}
	}

	public static function calculateLatestProcessingDateWithDepositDateLessThanOrEqualTo($dt, $paymentType) {
		$dt = clone $dt; // prevent parameter object from being modified

		switch ($paymentType) {
			case self::BANKCARD:
				if (!BankingReconciliation::isBankingDay($dt)){  // if NOT a banking day, recurse after subtracting 1 day
					return self::calculateLatestProcessingDateWithDepositDateLessThanOrEqualTo($dt->sub(new DateInterval('P1D')), $paymentType);
				} else {
					 // Subtract 2 calendar days
					return $dt->sub(new DateInterval('P2D'));
				}
				break;
			case self::AMEX:
				if (!BankingReconciliation::isBankingDay($dt)){  // if NOT a banking day, recurse subtracting 1  day
					return self::calculateEarliestProcessingDateWithDepositDateGreaterThanOrEqualTo($dt->sub(new DateInterval('P1D')), $paymentType);
				} else {
					 // Subtract 3 calendar days
					return $dt->sub(new DateInterval('P3D'));
				}
				break;
			default:
				throw new BankingReconciliationException("Invalid paymentType. Please pass in const AMEX or BANKCARD");
		}
	}


	public static function calculateEffectiveDate($dt, $paymentType) {
		$dt = clone $dt; // prevent parameter object from being modified

		switch ($paymentType) {
			case self::BANKCARD:
				// add 1 calendar day, then the next banking day
				return self::addBankingDays($dt->add(new DateInterval('P1D')), 1);
				break;
			case self::AMEX:
				// add 2 calendar days, then the next banking day
				return self::addBankingDays($dt->add(new DateInterval('P2D')), 1);
				break;
			case self::ACH:
				// if a bank day, return same day, else next banking day
				if (self::isBankingDay($dt)) {
					return $dt;
				} else {
					return self::addBankingDays($dt, 1);
				}
				break;
			default:
				throw new BankingReconciliationException("Invalid paymentType. Please pass in const AMEX, BANKCARD, or ACH");
		}
	}

	public static function subtractBankingDays($dt, $numDays) {
		if (!is_integer($numDays) || $numDays < 0) {
			throw new BankingReconciliationException("NumDays must be a positive integer.  Passed in:" . strVal($numDays));
		}

		while ($numDays > 0) {
				do {
					$dt = $dt->sub(new DateInterval('P1D'));
				} while (!self::isBankingDay($dt));
			$numDays--;
		}
		return $dt;
	}

	public static function addBankingDays($dt, $numDays) {
		if (!is_integer($numDays) || $numDays < 0) {
			throw new BankingReconciliationException("NumDays must be a positive integer.  Passed in:" . strVal($numDays));
		}

		while ($numDays > 0) {
				do {
					$dt = $dt->add(new DateInterval('P1D'));
				} while (!self::isBankingDay($dt));
			$numDays--;
		}
		return $dt;
	}
}
?>
