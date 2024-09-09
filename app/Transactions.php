<?php
namespace App;
use InvalidArgumentException;
use Exception;


class Transactions
{

    /**
     * Calculates the change to be returned based on the amount given, total cost, and available denominations.
     *
     * @param int|int[] $amountGiven The amount given as a single integer or an array of integers.
     * @param float $totalCost The total cost of the transaction.
     * @param int[] $denominations An array of available denominations.
     * 
     * @return int[] An associative array where the keys are denominations and the values are the number of bills of each denomination.
     * 
     * @throws \InvalidArgumentException If the input types are incorrect or if the denominations array is empty.
     * @throws \Exception If there is an insufficient amount or if the exact change cannot be made due to insufficient denominations.
     * 
     * @see validateInputs()
     * @see calculateTotalAmountGiven()
     */
    public function getChange($amountGiven, float $totalCost, array $denominations)
    {
        $changes = 0;
        $results = [];

        //validate inputs
        $this->validateInputs($amountGiven, $totalCost, $denominations);

        //calculate the total amount given
        $totalAmountGiven = $this->calculateTotalAmountGiven($amountGiven);

        //check if the $totalAmountGiven is greater than or equal to the $totalCost
        if ($totalAmountGiven < $totalCost) {
            throw new Exception("Insufficient amount.");
        }

        //sort $denominations in descending order
        rsort($denominations);

        //calculation of the tickets to be returned
        $changes = $totalAmountGiven - $totalCost;

        //return an empty array if no change is required
        if ($changes == 0) {
            return [];
        }
        echo "Given:" . json_encode($amountGiven) . "\n";
        echo "Cost: $totalCost \n";
        echo "Changes: $changes \n";

        foreach ($denominations as $bills) {

            if ($changes >= $bills) {

                $billCount = intdiv($changes, $bills);

                $changes = $changes % $bills;

                $results[$bills] = $billCount;

                //stop the operation if the remaining amount to be paid is equal to 0
                if ($changes == 0)
                    break;
            }
        }

        //If the exchange value is not 0, we will throw an error because it means there are not enough denominations in the inventory.
        if ($changes != 0) {
            throw new Exception("The exact change cannot be made due to insufficient denominations in the inventory.");
        }

        echo "Output:" . json_encode($results) . "\n";
        return $results;
    }


    /**
     * Calculates the change to be returned based on the amount given, total cost, and available cash inventory.
     * Updates the cash inventory accordingly.
     *
     * @param int|int[] $amountGiven The amount given as a single integer or an array of integers.
     * @param float $totalCost The total cost of the transaction.
     * @param array &$cash_inventory An associative array representing the cash inventory, where keys are denominations and values are counts of those denominations.
     * 
     * @return int[] An associative array where the keys are denominations and the values are the number of bills of each denomination to be returned as change.
     * 
     * @throws \InvalidArgumentException If the input types are incorrect or if the cash inventory array is empty.
     * @throws \Exception If there is an insufficient amount or if the exact change cannot be made due to insufficient denominations in the inventory.
     * 
     * @see validateInputs()
     * @see calculateTotalAmountGiven()
     * @see updateCashInventory()
     */
    public function getChangeWithInventory($amountGiven, float $totalCost, array &$cash_inventory)
    {
        $changes = 0;
        $results = [];
        $cash_inventory_copy = [];

        //validate inputs
        $this->validateInputs($amountGiven, $totalCost, $cash_inventory);

        //calculate the total amount given
        $totalAmountGiven = $this->calculateTotalAmountGiven($amountGiven);

        //check if the $totalAmountGiven is greater than or equal to the $totalCost
        if ($totalAmountGiven < $totalCost) {
            throw new Exception("Insufficient amount.");
        }

        //sort $denominations in descending order
        krsort($cash_inventory);

        // create a copy of $cash_inventory for processing. Update this copy instead of the original, and use it if all operation pass.
        $cash_inventory_copy = $cash_inventory;

        //calculation of the tickets to be returned
        $changes = $totalAmountGiven - $totalCost;

        //return an empty array if no change is required
        if ($changes == 0) {
            return [];
        }
        echo "Given:" . json_encode($amountGiven) . "\n";
        echo "Cost: $totalCost \n";
        echo "Changes: $changes \n";

        foreach ($cash_inventory as $bills => $quantity) {
            if ($changes >= $bills) {
                $billCount = intdiv($changes, $bills);
                //check if the number of bills in the inventory is sufficient for the changes
                if ($quantity >= $billCount) {
                    $changes = $changes % $bills;
                    $results[$bills] = $billCount;

                    //update the cash_inventory
                    $cash_inventory_copy[$bills] -= $billCount;
                } else {
                    $changes -= ($bills * $quantity);
                    $results[$bills] = $quantity;

                    //update the cash_inventory
                    $cash_inventory_copy[$bills] -= $quantity;
                }
                //stop the operation if the remaining amount to be paid is equal to 0
                if ($changes == 0)
                    break;
            }
        }

        //If the exchange value is not 0, we will throw an error because it means there are not enough denominations in the inventory.
        if ($changes != 0) {
            throw new Exception("The exact change cannot be made due to insufficient denominations in the inventory.");
        }

        //update the stock of available denominations
        $this->updateCashInventory($amountGiven, $cash_inventory_copy);
        //update the real $cash_inventory
        $cash_inventory = $cash_inventory_copy;

        echo "Output:" . json_encode($results) . "\n";
        krsort($cash_inventory);
        echo "Updated Inventory:" . json_encode($cash_inventory) . "\n";
        return $results;
    }


    private function validateInputs($amountGiven, float $totalCost, array $denominations): void
    {   
        //check if the $amountGiven type is not an int or an array
        if (!is_int($amountGiven) && !is_array($amountGiven)) {
            throw new InvalidArgumentException('The type of the amount given must be an int or an array');
        }

        //check if the $totalCost type is not a float
        if (!is_float($totalCost)) {
            throw new InvalidArgumentException('The type of the total cost  must be a float');
        }

        //check if the $denominations type is not an array or empty
        if (!is_array($denominations) || empty($denominations)) {
            throw new InvalidArgumentException('The type of denominations must be an array and must not be empty');
        }
    }

    public function calculateTotalAmountGiven($amountGiven): int
    {
        $totalAmountGiven = 0;
        //In the case where the input type is an array, we need to check if the values inside it are of integer type    
        if (is_array($amountGiven)) {
            foreach ($amountGiven as $value) {
                if (!is_int($value)) {
                    throw new InvalidArgumentException('The type of the amount given must be an int');
                }
                $totalAmountGiven += $value;
            }
        } else {
            $totalAmountGiven = $amountGiven;
        }
        return $totalAmountGiven;
    }

    private function updateCashInventory($amountGiven, array &$cashInventory): void
    {
        if (is_array($amountGiven)) {
            foreach ($amountGiven as $amount) {
                $this->updateInventory($amount, $cashInventory);
            }
        } else {
            $this->updateInventory($amountGiven, $cashInventory);
        }
    }


    // NOTE: Function used to update inventory for a single amount
    private function updateInventory($amount, &$cashInventory): void
    {
        //check if the amount already exists in the inventory
        if (array_key_exists($amount, $cashInventory)) {
            //increment the count of the amount
            $cashInventory[$amount] += 1;
        } else {
            //initialize the count for the amount
            $cashInventory[$amount] = 1;
        }
    }
}

//PART I
echo "\nPART I \n";
$test = new Transactions();
$test->getChange(80, 60, [100, 20, 50, 5]);

//PART II
echo "\nPART II \n\n";

$cash_inventory = [100 => 5, 50 => 3, 20 => 4, 10 => 2];

//First purchase
echo "First purchase: \n";
$test->getChangeWithInventory(50, 20, $cash_inventory);

//Second purchase
echo "\n\nSecond purchase: \n";
$test->getChangeWithInventory(200, 40, $cash_inventory);

?>