<?php

namespace Rahabit\Payment\Gateways;

interface GatewayInterface
{
    public function getName(): string;

    public function initialize(array $parameters = []): self;

    public function purchase(): void;

    public function purchaseUri(): string;

    public function verify(): void;
}
