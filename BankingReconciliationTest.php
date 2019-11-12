<?php

require_once 'BankingReconciliation.php';

use PHPUnit\Framework\TestCase;

class BankingReconciliationTest extends TestCase
{
    /**
     * @dataProvider invalidTypesDP
     */
    public function testInvalidTypeException($input) {
    	$this->expectException("BankingReconciliation_Exception");
        BankingReconciliation::isBankingDay($input);
           
    }

    public function invalidTypesDP() {
    	return [
	   		["hello"],
	    	["2019-10-01"],
	    	[2019],
	    	[null]
    	];
    }

    /**
     * @dataProvider outOfBoundsDatesDP
     */
    public function testOutOfBoundsDatesException($input) {
    	$this->expectException("BankingReconciliation_Exception");
        BankingReconciliation::isBankingDay($input);
    }

    public function outOfBoundsDatesDP() {
    	return [
	    	[new DateTime("2001-01-01")],
	    	[new DateTime("2024-12-10")],
    	];
    }

    /**
     * @dataProvider validDatesDP
     */
    public function testValidDates($input, $expectedResult) {
            $this->assertEquals(BankingReconciliation::isBankingDay($input), $expectedResult);      
    }

    public function validDatesDP() {
	    return [
    		[new DateTime("2019-11-02"), false], // weekend
    		[new DateTime("2019-11-03"), false], // weekend
    		[new DateTime("2019-11-04"), true],
    		[new DateTime("2019-11-05"), true],
    		[new DateTime("2019-11-06"), true],
    		[new DateTime("2019-11-07"), true],
    		[new DateTime("2019-11-08"), true],
    		[new DateTime("2019-11-11"), false], // bank holiday
    		[new DateTime("2019-01-01"), false], // bank holiday
		];
    }

    /**
     * @dataProvider subtractBankingDaysDP
     */
    public function testSubtractBankingDays($dt, $numDays, $expectedResult) {
            $this->assertEquals(BankingReconciliation::subtractBankingDays($dt, $numDays), $expectedResult);      
    }

    public function subtractBankingDaysDP() {
	    return [
    		[new DateTime("2019-11-04"), 0, new DateTime("2019-11-04")],
    		[new DateTime("2019-11-04"), 1, new DateTime("2019-11-01")],
    		[new DateTime("2019-11-04"), 2, new DateTime("2019-10-31")],
    		[new DateTime("2019-11-13"), 2, new DateTime("2019-11-08")],
    		[new DateTime("2019-12-27"), 5, new DateTime("2019-12-19")],
		];
    }


    /**
     * @dataProvider addBankingDaysDP
     */
    public function testAddBankingDays($dt, $numDays, $expectedResult) {
            $this->assertEquals(BankingReconciliation::addBankingDays($dt, $numDays), $expectedResult);      
    }

    public function addBankingDaysDP() {
	    return [
    		[new DateTime("2019-11-01"), 1, new DateTime("2019-11-04")],
    		[new DateTime("2019-11-01"), 2, new DateTime("2019-11-05")],
    		[new DateTime("2019-11-13"), 2, new DateTime("2019-11-15")],
    		[new DateTime("2019-12-24"), 1, new DateTime("2019-12-26")],
		];
    }


	/**
     * @dataProvider getEarliestProcessingDateWithDepositDateGreaterThanOrEqualToDP
     */
    public function testGetEarliestProcessingDateWithDepositDateGreaterThanOrEqualTo($inputDate, $inputCardType, $expectedResult) {
            $this->assertEquals(BankingReconciliation::getEarliestProcessingDateWithDepositDateGreaterThanOrEqualTo($inputDate, $inputCardType), $expectedResult);      
    }

    public function getEarliestProcessingDateWithDepositDateGreaterThanOrEqualToDP() {
	    return [
    		[new DateTime("2019-08-01"), BankingReconciliation::BANKCARD, new DateTime("2019-07-30")], // Thurs returns Tues
    		[new DateTime("2019-08-02"), BankingReconciliation::BANKCARD, new DateTime("2019-07-31")], // Fri   returns Wed
    		[new DateTime("2019-08-03"), BankingReconciliation::BANKCARD, new DateTime("2019-08-01")], // Sat   returns Thurs
    		[new DateTime("2019-08-04"), BankingReconciliation::BANKCARD, new DateTime("2019-08-01")], // Sun   returns Thurs
    		[new DateTime("2019-08-05"), BankingReconciliation::BANKCARD, new DateTime("2019-08-01")], // Mon   returns Thurs
    		[new DateTime("2019-08-06"), BankingReconciliation::BANKCARD, new DateTime("2019-08-04")], // Tues  returns Sun
    		[new DateTime("2019-08-07"), BankingReconciliation::BANKCARD, new DateTime("2019-08-05")], // Wed   returns Mon

    		[new DateTime("2019-08-01"), BankingReconciliation::AMEX, new DateTime("2019-07-29")], // Thurs returns Mon
    		[new DateTime("2019-08-02"), BankingReconciliation::AMEX, new DateTime("2019-07-30")], // Fri   returns Tues
    		[new DateTime("2019-08-03"), BankingReconciliation::AMEX, new DateTime("2019-07-30")], // Sat   returns Tues
    		[new DateTime("2019-08-04"), BankingReconciliation::AMEX, new DateTime("2019-07-30")], // Sun   returns Tues
    		[new DateTime("2019-08-05"), BankingReconciliation::AMEX, new DateTime("2019-07-31")], // Mon   returns Wed
    		[new DateTime("2019-08-06"), BankingReconciliation::AMEX, new DateTime("2019-08-03")], // Tues  returns Sat
    		[new DateTime("2019-08-07"), BankingReconciliation::AMEX, new DateTime("2019-08-04")], // Wed   returns Sun
    		
    		// TODO: add some bank holiday examples
		];
    }

    /**
     * @dataProvider getLatestProcessingDateWithDepositDateLessThanOrEqualToDP
     */
    public function testGetLatestProcessingDateWithDepositDateLessThanOrEqualTo($inputDate, $inputCardType, $expectedResult) {
            $this->assertEquals(BankingReconciliation::getLatestProcessingDateWithDepositDateLessThanOrEqualTo($inputDate, $inputCardType), $expectedResult);      
    }

    public function getLatestProcessingDateWithDepositDateLessThanOrEqualToDP() {
	    return [
    		[new DateTime("2019-08-01"), BankingReconciliation::BANKCARD, new DateTime("2019-07-30")], // Thurs returns Tues
    		[new DateTime("2019-08-02"), BankingReconciliation::BANKCARD, new DateTime("2019-07-31")], // Fri   returns Wed
    		[new DateTime("2019-08-03"), BankingReconciliation::BANKCARD, new DateTime("2019-07-31")], // Sat   returns Wed
    		[new DateTime("2019-08-04"), BankingReconciliation::BANKCARD, new DateTime("2019-07-31")], // Sun   returns Wed
    		[new DateTime("2019-08-05"), BankingReconciliation::BANKCARD, new DateTime("2019-08-03")], // Mon   returns Sat
    		[new DateTime("2019-08-06"), BankingReconciliation::BANKCARD, new DateTime("2019-08-04")], // Tues  returns Sun
    		[new DateTime("2019-08-07"), BankingReconciliation::BANKCARD, new DateTime("2019-08-05")], // Wed   returns Mon

    		[new DateTime("2019-08-01"), BankingReconciliation::AMEX, new DateTime("2019-07-29")], // Thurs returns Mon
    		[new DateTime("2019-08-02"), BankingReconciliation::AMEX, new DateTime("2019-07-30")], // Fri   returns Tues
    		[new DateTime("2019-08-03"), BankingReconciliation::AMEX, new DateTime("2019-07-30")], // Sat   returns Tues
    		[new DateTime("2019-08-04"), BankingReconciliation::AMEX, new DateTime("2019-07-30")], // Sun   returns Tues
    		[new DateTime("2019-08-05"), BankingReconciliation::AMEX, new DateTime("2019-08-02")], // Mon   returns Fri
    		[new DateTime("2019-08-06"), BankingReconciliation::AMEX, new DateTime("2019-08-03")], // Tues  returns Sat
    		[new DateTime("2019-08-07"), BankingReconciliation::AMEX, new DateTime("2019-08-04")], // Wed   returns Sun

    		// TODO: add some bank holiday examples
		];
    }
}
?>