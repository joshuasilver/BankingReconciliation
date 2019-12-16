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
     * @dataProvider calculateEarliestProcessingDateWithDepositDateGreaterThanOrEqualToDP
     */
    public function testCalculateEarliestProcessingDateWithDepositDateGreaterThanOrEqualTo($inputDate, $inputPaymentType, $expectedResult) {
        $this->assertEquals(BankingReconciliation::calculateEarliestProcessingDateWithDepositDateGreaterThanOrEqualTo($inputDate, $inputPaymentType), $expectedResult);
    }

    public function calculateEarliestProcessingDateWithDepositDateGreaterThanOrEqualToDP() {
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
     * @dataProvider calculateLatestProcessingDateWithDepositDateLessThanOrEqualToDP
     */
    public function testCalculateLatestProcessingDateWithDepositDateLessThanOrEqualTo($inputDate, $inputPaymentType, $expectedResult) {
            $this->assertEquals(BankingReconciliation::calculateLatestProcessingDateWithDepositDateLessThanOrEqualTo($inputDate, $inputPaymentType), $expectedResult);
    }

    public function calculateLatestProcessingDateWithDepositDateLessThanOrEqualToDP() {
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

    /**
     * @dataProvider calculateEffectiveDateDP
     */
    public function testCalculateEffectiveDate($inputDate, $inputPaymentType, $expectedResult) {
        $this->assertEquals(BankingReconciliation::calculateEffectiveDate($inputDate, $inputPaymentType), $expectedResult);
    }

    public function calculateEffectiveDateDP() {
        return [
            [new DateTime("2019-08-01"), BankingReconciliation::BANKCARD, new DateTime("2019-08-05")], // Thurs returns Mon
            [new DateTime("2019-08-02"), BankingReconciliation::BANKCARD, new DateTime("2019-08-05")], // Fri   returns Mon
            [new DateTime("2019-08-03"), BankingReconciliation::BANKCARD, new DateTime("2019-08-05")], // Sat   returns Mon
            [new DateTime("2019-08-04"), BankingReconciliation::BANKCARD, new DateTime("2019-08-06")], // Sun   returns Tues
            [new DateTime("2019-08-05"), BankingReconciliation::BANKCARD, new DateTime("2019-08-07")], // Mon   returns Wed
            [new DateTime("2019-08-06"), BankingReconciliation::BANKCARD, new DateTime("2019-08-08")], // Tues  returns Thur
            [new DateTime("2019-08-07"), BankingReconciliation::BANKCARD, new DateTime("2019-08-09")], // Wed   returns Fri


            // Before the 2019-10-19 Amex Settlement change:
            [new DateTime("2019-07-01"), BankingReconciliation::AMEX, new DateTime("2019-07-05")], //Mon   returns Fri
            [new DateTime("2019-07-02"), BankingReconciliation::AMEX, new DateTime("2019-07-05")], //Tue   returns Fri
            [new DateTime("2019-07-03"), BankingReconciliation::AMEX, new DateTime("2019-07-08")], //Wed   returns Mon
            [new DateTime("2019-07-04"), BankingReconciliation::AMEX, new DateTime("2019-07-08")], //Thu   returns Mon
            [new DateTime("2019-07-05"), BankingReconciliation::AMEX, new DateTime("2019-07-08")], //Fri   returns Mon
            [new DateTime("2019-07-06"), BankingReconciliation::AMEX, new DateTime("2019-07-09")], //Sat   returns Tue
            [new DateTime("2019-07-07"), BankingReconciliation::AMEX, new DateTime("2019-07-10")], //Sun   returns Wed
            [new DateTime("2019-07-08"), BankingReconciliation::AMEX, new DateTime("2019-07-11")], //Mon   returns Thu
            [new DateTime("2019-07-09"), BankingReconciliation::AMEX, new DateTime("2019-07-12")], //Tue   returns Fri
            [new DateTime("2019-07-10"), BankingReconciliation::AMEX, new DateTime("2019-07-15")], //Wed   returns Mon
            [new DateTime("2019-07-11"), BankingReconciliation::AMEX, new DateTime("2019-07-15")], //Thu   returns Mon
            [new DateTime("2019-07-12"), BankingReconciliation::AMEX, new DateTime("2019-07-15")], //Fri   returns Mon
            [new DateTime("2019-07-13"), BankingReconciliation::AMEX, new DateTime("2019-07-16")], //Sat   returns Tue
            [new DateTime("2019-07-14"), BankingReconciliation::AMEX, new DateTime("2019-07-17")], //Sun   returns Wed
            [new DateTime("2019-07-15"), BankingReconciliation::AMEX, new DateTime("2019-07-18")], //Mon   returns Thu
            [new DateTime("2019-07-16"), BankingReconciliation::AMEX, new DateTime("2019-07-19")], //Tue   returns Fri
            [new DateTime("2019-07-17"), BankingReconciliation::AMEX, new DateTime("2019-07-22")], //Wed   returns Mon
            [new DateTime("2019-07-18"), BankingReconciliation::AMEX, new DateTime("2019-07-22")], //Thu   returns Mon
            [new DateTime("2019-07-19"), BankingReconciliation::AMEX, new DateTime("2019-07-22")], //Fri   returns Mon
            [new DateTime("2019-07-20"), BankingReconciliation::AMEX, new DateTime("2019-07-23")], //Sat   returns Tue
            [new DateTime("2019-07-21"), BankingReconciliation::AMEX, new DateTime("2019-07-24")], //Sun   returns Wed
            [new DateTime("2019-07-22"), BankingReconciliation::AMEX, new DateTime("2019-07-25")], //Mon   returns Thu
            [new DateTime("2019-07-23"), BankingReconciliation::AMEX, new DateTime("2019-07-26")], //Tue   returns Fri
            [new DateTime("2019-07-24"), BankingReconciliation::AMEX, new DateTime("2019-07-29")], //Wed   returns Mon
            [new DateTime("2019-07-25"), BankingReconciliation::AMEX, new DateTime("2019-07-29")], //Thu   returns Mon
            [new DateTime("2019-07-26"), BankingReconciliation::AMEX, new DateTime("2019-07-29")], //Fri   returns Mon
            [new DateTime("2019-07-27"), BankingReconciliation::AMEX, new DateTime("2019-07-30")], //Sat   returns Tue
            [new DateTime("2019-07-28"), BankingReconciliation::AMEX, new DateTime("2019-07-31")], //Sun   returns Wed
            [new DateTime("2019-07-29"), BankingReconciliation::AMEX, new DateTime("2019-08-01")], //Mon   returns Thu
            [new DateTime("2019-07-30"), BankingReconciliation::AMEX, new DateTime("2019-08-02")], //Tue   returns Fri
            [new DateTime("2019-07-31"), BankingReconciliation::AMEX, new DateTime("2019-08-05")], //Wed   returns Mon
            [new DateTime("2019-08-01"), BankingReconciliation::AMEX, new DateTime("2019-08-05")], //Thu   returns Mon
            [new DateTime("2019-08-02"), BankingReconciliation::AMEX, new DateTime("2019-08-05")], //Fri   returns Mon
            [new DateTime("2019-08-03"), BankingReconciliation::AMEX, new DateTime("2019-08-06")], //Sat   returns Tue
            [new DateTime("2019-08-04"), BankingReconciliation::AMEX, new DateTime("2019-08-07")], //Sun   returns Wed
            [new DateTime("2019-08-05"), BankingReconciliation::AMEX, new DateTime("2019-08-08")], //Mon   returns Thu
            [new DateTime("2019-08-06"), BankingReconciliation::AMEX, new DateTime("2019-08-09")], //Tue   returns Fri
            [new DateTime("2019-08-07"), BankingReconciliation::AMEX, new DateTime("2019-08-12")], //Wed   returns Mon
            [new DateTime("2019-08-08"), BankingReconciliation::AMEX, new DateTime("2019-08-12")], //Thu   returns Mon
            [new DateTime("2019-08-09"), BankingReconciliation::AMEX, new DateTime("2019-08-12")], //Fri   returns Mon
            [new DateTime("2019-08-10"), BankingReconciliation::AMEX, new DateTime("2019-08-13")], //Sat   returns Tue
            [new DateTime("2019-08-11"), BankingReconciliation::AMEX, new DateTime("2019-08-14")], //Sun   returns Wed
            [new DateTime("2019-08-12"), BankingReconciliation::AMEX, new DateTime("2019-08-15")], //Mon   returns Thu
            [new DateTime("2019-08-13"), BankingReconciliation::AMEX, new DateTime("2019-08-16")], //Tue   returns Fri
            [new DateTime("2019-08-14"), BankingReconciliation::AMEX, new DateTime("2019-08-19")], //Wed   returns Mon
            [new DateTime("2019-08-15"), BankingReconciliation::AMEX, new DateTime("2019-08-19")], //Thu   returns Mon
            [new DateTime("2019-08-16"), BankingReconciliation::AMEX, new DateTime("2019-08-19")], //Fri   returns Mon
            [new DateTime("2019-08-17"), BankingReconciliation::AMEX, new DateTime("2019-08-20")], //Sat   returns Tue
            [new DateTime("2019-08-18"), BankingReconciliation::AMEX, new DateTime("2019-08-21")], //Sun   returns Wed
            [new DateTime("2019-08-19"), BankingReconciliation::AMEX, new DateTime("2019-08-22")], //Mon   returns Thu
            [new DateTime("2019-08-20"), BankingReconciliation::AMEX, new DateTime("2019-08-23")], //Tue   returns Fri
            [new DateTime("2019-08-21"), BankingReconciliation::AMEX, new DateTime("2019-08-26")], //Wed   returns Mon
            [new DateTime("2019-08-22"), BankingReconciliation::AMEX, new DateTime("2019-08-26")], //Thu   returns Mon
            [new DateTime("2019-08-23"), BankingReconciliation::AMEX, new DateTime("2019-08-26")], //Fri   returns Mon
            [new DateTime("2019-08-24"), BankingReconciliation::AMEX, new DateTime("2019-08-27")], //Sat   returns Tue
            [new DateTime("2019-08-25"), BankingReconciliation::AMEX, new DateTime("2019-08-28")], //Sun   returns Wed
            [new DateTime("2019-08-26"), BankingReconciliation::AMEX, new DateTime("2019-08-29")], //Mon   returns Thu
            [new DateTime("2019-08-27"), BankingReconciliation::AMEX, new DateTime("2019-08-30")], //Tue   returns Fri
            [new DateTime("2019-08-28"), BankingReconciliation::AMEX, new DateTime("2019-09-03")], //Wed   returns Tue
            [new DateTime("2019-08-29"), BankingReconciliation::AMEX, new DateTime("2019-09-03")], //Thu   returns Tue
            [new DateTime("2019-08-30"), BankingReconciliation::AMEX, new DateTime("2019-09-03")], //Fri   returns Tue
            [new DateTime("2019-08-31"), BankingReconciliation::AMEX, new DateTime("2019-09-03")], //Sat   returns Tue
            [new DateTime("2019-09-01"), BankingReconciliation::AMEX, new DateTime("2019-09-04")], //Sun   returns Wed
            [new DateTime("2019-09-02"), BankingReconciliation::AMEX, new DateTime("2019-09-05")], //Mon   returns Thu
            [new DateTime("2019-09-03"), BankingReconciliation::AMEX, new DateTime("2019-09-06")], //Tue   returns Fri
            [new DateTime("2019-09-04"), BankingReconciliation::AMEX, new DateTime("2019-09-09")], //Wed   returns Mon
            [new DateTime("2019-09-05"), BankingReconciliation::AMEX, new DateTime("2019-09-09")], //Thu   returns Mon
            [new DateTime("2019-09-06"), BankingReconciliation::AMEX, new DateTime("2019-09-09")], //Fri   returns Mon
            [new DateTime("2019-09-07"), BankingReconciliation::AMEX, new DateTime("2019-09-10")], //Sat   returns Tue
            [new DateTime("2019-09-08"), BankingReconciliation::AMEX, new DateTime("2019-09-11")], //Sun   returns Wed
            [new DateTime("2019-09-09"), BankingReconciliation::AMEX, new DateTime("2019-09-12")], //Mon   returns Thu
            [new DateTime("2019-09-10"), BankingReconciliation::AMEX, new DateTime("2019-09-13")], //Tue   returns Fri
            [new DateTime("2019-09-11"), BankingReconciliation::AMEX, new DateTime("2019-09-16")], //Wed   returns Mon
            [new DateTime("2019-09-12"), BankingReconciliation::AMEX, new DateTime("2019-09-16")], //Thu   returns Mon
            [new DateTime("2019-09-13"), BankingReconciliation::AMEX, new DateTime("2019-09-16")], //Fri   returns Mon
            [new DateTime("2019-09-14"), BankingReconciliation::AMEX, new DateTime("2019-09-17")], //Sat   returns Tue
            [new DateTime("2019-09-15"), BankingReconciliation::AMEX, new DateTime("2019-09-18")], //Sun   returns Wed
            [new DateTime("2019-09-16"), BankingReconciliation::AMEX, new DateTime("2019-09-19")], //Mon   returns Thu
            [new DateTime("2019-09-17"), BankingReconciliation::AMEX, new DateTime("2019-09-20")], //Tue   returns Fri
            [new DateTime("2019-09-18"), BankingReconciliation::AMEX, new DateTime("2019-09-23")], //Wed   returns Mon
            [new DateTime("2019-09-19"), BankingReconciliation::AMEX, new DateTime("2019-09-23")], //Thu   returns Mon
            [new DateTime("2019-09-20"), BankingReconciliation::AMEX, new DateTime("2019-09-23")], //Fri   returns Mon
            [new DateTime("2019-09-21"), BankingReconciliation::AMEX, new DateTime("2019-09-24")], //Sat   returns Tue
            [new DateTime("2019-09-22"), BankingReconciliation::AMEX, new DateTime("2019-09-25")], //Sun   returns Wed
            [new DateTime("2019-09-23"), BankingReconciliation::AMEX, new DateTime("2019-09-26")], //Mon   returns Thu
            [new DateTime("2019-09-24"), BankingReconciliation::AMEX, new DateTime("2019-09-27")], //Tue   returns Fri
            [new DateTime("2019-09-25"), BankingReconciliation::AMEX, new DateTime("2019-09-30")], //Wed   returns Mon
            [new DateTime("2019-09-26"), BankingReconciliation::AMEX, new DateTime("2019-09-30")], //Thu   returns Mon
            [new DateTime("2019-09-27"), BankingReconciliation::AMEX, new DateTime("2019-09-30")], //Fri   returns Mon
            [new DateTime("2019-09-28"), BankingReconciliation::AMEX, new DateTime("2019-10-01")], //Sat   returns Tue
            [new DateTime("2019-09-29"), BankingReconciliation::AMEX, new DateTime("2019-10-02")], //Sun   returns Wed
            [new DateTime("2019-09-30"), BankingReconciliation::AMEX, new DateTime("2019-10-03")], //Mon   returns Thu
            [new DateTime("2019-10-01"), BankingReconciliation::AMEX, new DateTime("2019-10-04")], //Tue   returns Fri
            [new DateTime("2019-10-02"), BankingReconciliation::AMEX, new DateTime("2019-10-07")], //Wed   returns Mon
            [new DateTime("2019-10-03"), BankingReconciliation::AMEX, new DateTime("2019-10-07")], //Thu   returns Mon
            [new DateTime("2019-10-04"), BankingReconciliation::AMEX, new DateTime("2019-10-07")], //Fri   returns Mon
            [new DateTime("2019-10-05"), BankingReconciliation::AMEX, new DateTime("2019-10-08")], //Sat   returns Tue
            [new DateTime("2019-10-06"), BankingReconciliation::AMEX, new DateTime("2019-10-09")], //Sun   returns Wed
            [new DateTime("2019-10-07"), BankingReconciliation::AMEX, new DateTime("2019-10-10")], //Mon   returns Thu
            [new DateTime("2019-10-08"), BankingReconciliation::AMEX, new DateTime("2019-10-11")], //Tue   returns Fri
            [new DateTime("2019-10-09"), BankingReconciliation::AMEX, new DateTime("2019-10-15")], //Wed   returns Tue
            [new DateTime("2019-10-10"), BankingReconciliation::AMEX, new DateTime("2019-10-15")], //Thu   returns Tue
            [new DateTime("2019-10-11"), BankingReconciliation::AMEX, new DateTime("2019-10-15")], //Fri   returns Tue
            [new DateTime("2019-10-12"), BankingReconciliation::AMEX, new DateTime("2019-10-15")], //Sat   returns Tue
            [new DateTime("2019-10-13"), BankingReconciliation::AMEX, new DateTime("2019-10-16")], //Sun   returns Wed
            [new DateTime("2019-10-14"), BankingReconciliation::AMEX, new DateTime("2019-10-17")], //Mon   returns Thu
            [new DateTime("2019-10-15"), BankingReconciliation::AMEX, new DateTime("2019-10-18")], //Tue   returns Fri
            [new DateTime("2019-10-16"), BankingReconciliation::AMEX, new DateTime("2019-10-21")], //Wed   returns Mon
            [new DateTime("2019-10-17"), BankingReconciliation::AMEX, new DateTime("2019-10-21")], //Thu   returns Mon
            [new DateTime("2019-10-18"), BankingReconciliation::AMEX, new DateTime("2019-10-21")], //Fri   returns Mon


            // For some unknown reason, Amex changed their deposit schedule on 2019-10-19. These new values were verified against the actual bank account
            [new DateTime("2019-10-19"), BankingReconciliation::AMEX, new DateTime("2019-10-21")], //Sat   returns Mon
            [new DateTime("2019-10-20"), BankingReconciliation::AMEX, new DateTime("2019-10-22")], //Sun   returns Tue
            [new DateTime("2019-10-21"), BankingReconciliation::AMEX, new DateTime("2019-10-23")], //Mon   returns Wed
            [new DateTime("2019-10-22"), BankingReconciliation::AMEX, new DateTime("2019-10-24")], //Tue   returns Thu
            [new DateTime("2019-10-23"), BankingReconciliation::AMEX, new DateTime("2019-10-25")], //Wed   returns Fri
            [new DateTime("2019-10-24"), BankingReconciliation::AMEX, new DateTime("2019-10-28")], //Thu   returns Mon
            [new DateTime("2019-10-25"), BankingReconciliation::AMEX, new DateTime("2019-10-28")], //Fri   returns Mon
            [new DateTime("2019-10-26"), BankingReconciliation::AMEX, new DateTime("2019-10-28")], //Sat   returns Mon
            [new DateTime("2019-10-27"), BankingReconciliation::AMEX, new DateTime("2019-10-29")], //Sun   returns Tue
            [new DateTime("2019-10-28"), BankingReconciliation::AMEX, new DateTime("2019-10-30")], //Mon   returns Wed
            [new DateTime("2019-10-29"), BankingReconciliation::AMEX, new DateTime("2019-10-31")], //Tue   returns Thu
            [new DateTime("2019-10-30"), BankingReconciliation::AMEX, new DateTime("2019-11-01")], //Wed   returns Fri
            [new DateTime("2019-10-31"), BankingReconciliation::AMEX, new DateTime("2019-11-04")], //Thu   returns Mon
            [new DateTime("2019-11-01"), BankingReconciliation::AMEX, new DateTime("2019-11-04")], //Fri   returns Mon
            [new DateTime("2019-11-02"), BankingReconciliation::AMEX, new DateTime("2019-11-04")], //Sat   returns Mon
            [new DateTime("2019-11-03"), BankingReconciliation::AMEX, new DateTime("2019-11-05")], //Sun   returns Tue
            [new DateTime("2019-11-04"), BankingReconciliation::AMEX, new DateTime("2019-11-06")], //Mon   returns Wed
            [new DateTime("2019-11-05"), BankingReconciliation::AMEX, new DateTime("2019-11-07")], //Tue   returns Thu
            [new DateTime("2019-11-06"), BankingReconciliation::AMEX, new DateTime("2019-11-08")], //Wed   returns Fri
            [new DateTime("2019-11-07"), BankingReconciliation::AMEX, new DateTime("2019-11-12")], //Thu   returns Tue
            [new DateTime("2019-11-08"), BankingReconciliation::AMEX, new DateTime("2019-11-12")], //Fri   returns Tue
            [new DateTime("2019-11-09"), BankingReconciliation::AMEX, new DateTime("2019-11-12")], //Sat   returns Tue
            [new DateTime("2019-11-10"), BankingReconciliation::AMEX, new DateTime("2019-11-12")], //Sun   returns Tue
            [new DateTime("2019-11-11"), BankingReconciliation::AMEX, new DateTime("2019-11-13")], //Mon   returns Wed
            [new DateTime("2019-11-12"), BankingReconciliation::AMEX, new DateTime("2019-11-14")], //Tue   returns Thu
            [new DateTime("2019-11-13"), BankingReconciliation::AMEX, new DateTime("2019-11-15")], //Wed   returns Fri
            [new DateTime("2019-11-14"), BankingReconciliation::AMEX, new DateTime("2019-11-18")], //Thu   returns Mon
            [new DateTime("2019-11-15"), BankingReconciliation::AMEX, new DateTime("2019-11-18")], //Fri   returns Mon
            [new DateTime("2019-11-16"), BankingReconciliation::AMEX, new DateTime("2019-11-18")], //Sat   returns Mon
            [new DateTime("2019-11-17"), BankingReconciliation::AMEX, new DateTime("2019-11-19")], //Sun   returns Tue
            [new DateTime("2019-11-18"), BankingReconciliation::AMEX, new DateTime("2019-11-20")], //Mon   returns Wed
            [new DateTime("2019-11-19"), BankingReconciliation::AMEX, new DateTime("2019-11-21")], //Tue   returns Thu
            [new DateTime("2019-11-20"), BankingReconciliation::AMEX, new DateTime("2019-11-22")], //Wed   returns Fri
            [new DateTime("2019-11-21"), BankingReconciliation::AMEX, new DateTime("2019-11-25")], //Thu   returns Mon
            [new DateTime("2019-11-22"), BankingReconciliation::AMEX, new DateTime("2019-11-25")], //Fri   returns Mon
            [new DateTime("2019-11-23"), BankingReconciliation::AMEX, new DateTime("2019-11-25")], //Sat   returns Mon
            [new DateTime("2019-11-24"), BankingReconciliation::AMEX, new DateTime("2019-11-26")], //Sun   returns Tue
            [new DateTime("2019-11-25"), BankingReconciliation::AMEX, new DateTime("2019-11-27")], //Mon   returns Wed
            [new DateTime("2019-11-26"), BankingReconciliation::AMEX, new DateTime("2019-11-29")], //Tue   returns Fri
            [new DateTime("2019-11-27"), BankingReconciliation::AMEX, new DateTime("2019-11-29")], //Wed   returns Fri

            [new DateTime("2019-07-31"), BankingReconciliation::ACH, new DateTime("2019-07-31")], // Wed   returns Wed
            [new DateTime("2019-08-01"), BankingReconciliation::ACH, new DateTime("2019-08-01")], // Thurs returns Thurs
            [new DateTime("2019-08-02"), BankingReconciliation::ACH, new DateTime("2019-08-02")], // Fri   returns Fri
            [new DateTime("2019-08-03"), BankingReconciliation::ACH, new DateTime("2019-08-05")], // Sat   returns Mon
            [new DateTime("2019-08-04"), BankingReconciliation::ACH, new DateTime("2019-08-05")], // Sun   returns Mon
            [new DateTime("2019-08-05"), BankingReconciliation::ACH, new DateTime("2019-08-05")], // Mon   returns Mon
            [new DateTime("2019-08-06"), BankingReconciliation::ACH, new DateTime("2019-08-06")], // Tues  returns Tues

            // TODO: add some bank holiday examples
        ];
    }
}
?>