<?php
use PHPUnit\Framework\TestCase;
use App\Transactions;


class TransactionsTest extends TestCase
{
    public function testGetChangeThrowsExceptionForInvalidAmountGiven()
    {
        $transaction = new Transactions();
        $this->expectException(InvalidArgumentException::class);

        $transaction->getChange("invalid", 50, [100, 50, 20, 10]);
    }


    public function testAmountGivenIsLessThanToTheTotalCost()
    {
        $transaction = new Transactions();
        $totalCost = 100.00;
        $amountGiven = 50;
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Insufficient amount.");

        $transaction->getChange(50, 100, [100, 50, 20, 10]);
    }

    public function testGetChangeThrowsExceptionForInvalidDenominations()
    {
        $transaction = new Transactions();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The type of denominations must be an array and must not be empty");

        $transaction->getChange(100, 50, []);
    }

    public function testGetChangeReturnsCorrectChange()
    {   
        $transactions = new Transactions();
        $amountGiven = 80;
        $totalCost = 60;
        $denominations = [100, 20, 50, 5];

        $result = $transactions->getChange($amountGiven, $totalCost, $denominations);

        $expectedResult = [
            20 => 1
        ];
        $this->assertEquals($expectedResult, $result);
    }


    //PART II
    public function testGetChangeWithInventoryThrowsExceptionForInsufficientDenominations()
    {
        $transaction = new Transactions();
        $cash_inventory = [100 => 1, 20 => 1];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The exact change cannot be made due to insufficient denominations in the inventory.");

        $transaction->getChangeWithInventory(100, 50, $cash_inventory);
    }

    public function testGetChangeWithInventoryThrowsExceptionForInsufficientAmount()
    {
        $transaction = new Transactions();
        $cash_inventory = [50 => 2, 20 => 3];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Insufficient amount.");
        $transaction->getChangeWithInventory(40, 50, $cash_inventory);
    }


    public function testGetChangeWithInventoryUpdatesInventoryCorrectly()
    {
        $cash_inventory = [100 => 5, 50 => 3, 20 => 4, 10 => 2];
        $transactions=new Transactions();
        // First purchase
        $result1 = $transactions->getChangeWithInventory(50, 20, $cash_inventory);
        $expected_result1 = [
            20 => 1,
            10 => 1
        ];
        $expected_inventory_after_first_purchase = [
            100 => 5,
            50 => 4,
            20 => 3,
            10 => 1
        ];

        $this->assertEquals($expected_result1, $result1);
        $this->assertEquals($expected_inventory_after_first_purchase, $cash_inventory);

        // Second purchase
        $result2 = $transactions->getChangeWithInventory(200, 40, $cash_inventory);
        $expected_result2 = [
            100 => 1,
            50 => 1,
            10 => 1
        ];
        $expected_inventory_after_second_purchase = [
            200=>1,
            100 => 4,
            50 => 3,
            20 => 3,
            10 => 0
        ];

        $this->assertEquals($expected_result2, $result2);
        $this->assertEquals($expected_inventory_after_second_purchase, $cash_inventory);
    }



}
