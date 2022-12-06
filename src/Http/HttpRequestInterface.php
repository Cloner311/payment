<?php

namespace Rahabit\Payment\Http;

interface HttpRequestInterface
{
    public function addOption(string $name, $value) : HttpRequestInterface;
    public function execute($data = null);
    public function getInfo(string $name);
    public function close();
}
