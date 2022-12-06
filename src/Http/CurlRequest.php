<?php

namespace Rahabit\Payment\Http;

use Rahabit\Payment\Exceptions\GatewayException;
use Exception;

class CurlRequest implements HttpRequestInterface
{
    private $handle = null;
    private int $timeout = 30;
    private int $connectionTimeout = 60;

    public function __construct(string $url, string $method = "GET") {
        $this->handle = curl_init($url);
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);

        $timeout = app('config')->get('RahabitPayment.timeout');
        if ($timeout !== null) {
            $this->setTimeout($timeout);
        }

        $connectionTimeout = app('config')->get('RahabitPayment.connection_timeout');
        if ($connectionTimeout !== null) {
            $this->setConnectionTimeout($connectionTimeout);
        }
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function setConnectionTimeout(int $connection_timeout): self
    {
        $this->connectionTimeout = $connection_timeout;

        return $this;
    }

    public function addOption(string $name, $value): self
    {
        curl_setopt($this->handle, $name, $value);

        return $this;
    }

    public function execute($data = null)
    {
        try {
            if (!empty($data)) {
                curl_setopt($this->handle, CURLOPT_POSTFIELDS, $data);
                curl_setopt(
                    $this->handle,
                    CURLOPT_HTTPHEADER,
                    ['Content-Type: application/json', 'Content-Length: ' . strlen($data)]
                );
            }

            curl_setopt($this->handle, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);

            $result = curl_exec($this->handle);
            $ch_error = curl_error($this->handle);

            if ($ch_error) {
                throw GatewayException::connectionProblem(new Exception($ch_error));
            }

            $this->close();

            return $result;
        } catch (GatewayException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw GatewayException::connectionProblem($ex);
        }
    }

    public function getInfo(string $name)
    {
        return curl_getinfo($this->handle, $name);
    }

    public function close()
    {
        curl_close($this->handle);
    }
}
