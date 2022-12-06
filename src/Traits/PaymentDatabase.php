<?php

namespace Rahabit\Payment\Traits;

trait PaymentDatabase
{
    /**
     * RahabitPayment Table Name variable
     *
     * @var string
     */
    private string $RahabitPayment_table = 'RahabitPayment_transactions';

    /**
     * Get IranPayment Table Name function
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table = app('config')->get('RahabitPayment.table', $this->RahabitPayment_table);
    }
}
