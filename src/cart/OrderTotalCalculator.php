<?php

namespace SilverShop\Core;

use ErrorException;
use Exception;
use SS_Log;
use ClassInfo;


/**
 * Handles the calculation of order totals.
 *
 * Creates (if necessary) and calculates values for each modifier,
 * and subsequently the total of the order.
 * Caches to prevent recalculation, unless dirty.
 */
class OrderTotalCalculator
{
    protected $order;

    function __construct(Order $order)
    {
        $this->order = $order;
    }

    function calculate()
    {
        $runningtotal = $this->order->SubTotal();
        $modifiertotal = 0;
        $sort = 1;
        $existingmodifiers = $this->order->Modifiers();
        $modifierclasses = Order::config()->modifiers;
        //check if modifiers are even in use
        if (!is_array($modifierclasses) || empty($modifierclasses)) {
            return $runningtotal;
        }

        if (ShopTools::DBConn()->supportsTransactions()) {
            ShopTools::DBConn()->transactionStart();
        }

        set_error_handler(
            function ($severity, $message, $file, $line) {
                throw new ErrorException($message, 0, $severity, $file, $line);
            }, E_ALL & ~(E_STRICT | E_NOTICE)
        );

        try {
            foreach ($modifierclasses as $ClassName) {
                if ($modifier = $this->getModifier($ClassName)) {
                    $modifier->Sort = $sort;
                    $runningtotal = $modifier->modify($runningtotal);
                    if ($modifier->isChanged()) {
                        $modifier->write();
                    }
                }
                $sort++;
            }
            //clear old modifiers out
            if ($existingmodifiers) {
                foreach ($existingmodifiers as $modifier) {
                    if (!in_array($modifier->ClassName, $modifierclasses)) {
                        $modifier->delete();
                        $modifier->destroy();
                    }
                }
            }
        } catch (Exception $ex) {
            // Rollback the transaction if an error occurred
            if (ShopTools::DBConn()->supportsTransactions()) {
                ShopTools::DBConn()->transactionRollback();
            }
            // throw the exception after rollback
            throw $ex;
        } finally {
            // restore the error handler, no matter what
            restore_error_handler();
        }

        // Everything went through fine, complete the transaction
        if (ShopTools::DBConn()->supportsTransactions()) {
            ShopTools::DBConn()->transactionEnd();
        }

        //prevent negative sales from ever occurring
        if ($runningtotal < 0) {
            SS_Log::log(
                "Order (ID = {$this->order->ID}) was calculated to equal $runningtotal.\n
				Order totals should never be negative!\n
				The order total was set to $0",
                SS_Log::ERR
            );
            $runningtotal = 0;
        }

        return $runningtotal;
    }

    /**
     * Retrieve a modifier of a given class for the order.
     * Modifier will be retrieved from database if it already exists,
     * or created if it is always required.
     *
     * @param string  $className
     * @param boolean $forcecreate - force the modifier to be created.
     */
    public function getModifier($className, $forcecreate = false)
    {
        if (!ClassInfo::exists($className)) {
            user_error("Modifier class \"$className\" does not exist.");
        }
        //search for existing
        $modifier = $className::get()
            ->filter("OrderID", $this->order->ID)
            ->first();
        if ($modifier) {
            //remove if no longer valid
            if (!$modifier->valid()) {
                //TODO: need to provide feedback message - why modifier was removed
                $modifier->delete();
                $modifier->destroy();
                return null;
            }
            return $modifier;
        }
        $modifier = new $className();
        if ($modifier->required() || $forcecreate) { //create any modifiers that are required for every order
            $modifier->OrderID = $this->order->ID;
            $modifier->write();
            $this->order->Modifiers()->add($modifier);

            return $modifier;
        }

        return null;
    }
}
