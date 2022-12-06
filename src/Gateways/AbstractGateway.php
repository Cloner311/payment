<?php

namespace Rahabit\Payment\Gateways;

use Rahabit\Payment\Traits\UserData;
use Rahabit\Payment\Traits\PaymentData;
use Rahabit\Payment\Traits\TransactionData;

use Exception;
use Rahabit\Payment\Exceptions\InvalidDataException;
use Rahabit\Payment\Exceptions\RahabitPaymentException;
use Rahabit\Payment\Exceptions\SucceedRetryException;
use Rahabit\Payment\Exceptions\InvalidRequestException;
use Rahabit\Payment\Exceptions\TransactionFailedException;
use Rahabit\Payment\Exceptions\TransactionNotFoundException;

use  Rahabit\Payment\Models\RahabitPaymentTransaction;

use  Rahabit\Payment\Helpers\Currency;

/**
 * @method getName()
 * @method purchase()
 * @method purchaseUri()
 * @method verify()
 */
abstract class AbstractGateway
{
    use UserData,
        PaymentData,
        TransactionData;

    /**
     * Request variable
     *
     * @var array
     */
    protected array $request;

    /**
     * Gateway Request Options variable
     *
     * @var array
     */
    protected array $gateway_request_options = [];

    /**
     * Initialize Gateway function
     *
     * @param array $parameters
     * @return $this
     * @throws InvalidDataException
     */
    public function initialize(array $parameters = []): self
    {
        $this->setRequest($parameters['request'] ?? app('request')->all());

        $this->setCurrency($parameters['currency'] ?? app('config')->get('RahabitPayment.currency', Currency::IRR));

        $this->setCallbackUrl($parameters['callback_url'] ?? app('config')->get('RahabitPayment.callback-url'));

        $this->setGatewayRequestOptions(array_merge(
            [
                'timeout' => app('config')->get('RahabitPayment.timeout', 30),
                'connection_timeout' => app('config')->get('RahabitPayment.connection_timeout', 60),
            ],
            $parameters['gateway_request_options'] ?? [],
        ));

        return $this;
    }

    /**
     * Set Request function
     *
     * @param array $request
     * @return $this
     */
    public function setRequest(array $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get Request function
     *
     * @return array
     */
    public function getRequest(): array
    {
        return $this->request;
    }

    /**
     * Set Gateway Request Options function
     *
     * @param array $options
     * @return $this
     */
    public function setGatewayRequestOptions(array $options): self
    {
        $this->gateway_request_options = $options;

        return $this;
    }

    /**
     * Get Gateway Request Options function
     *
     * @return array
     */
    public function getGatewayRequestOptions(): array
    {
        return $this->gateway_request_options;
    }

    /**
     * @throws InvalidDataException
     */
    protected function prePurchase(): void
    {
        if ($this->preparedAmount() <= 0) {
            throw InvalidDataException::invalidAmount();
        }

        if (!in_array($this->getCurrency(), [Currency::IRR, Currency::IRT])) {
            throw InvalidDataException::invalidCurrency();
        }

        if (filter_var($this->preparedCallbackUrl(), FILTER_VALIDATE_URL) === false) {
            throw InvalidDataException::invalidCallbackUrl();
        }

        $this->newTransaction([
            'user_id' => $this->getUserId(),
            'full_name' => $this->getFullname(),
            'email' => $this->getEmail(),
            'mobile' => $this->getMobile(),
            'description' => $this->getDescription(),
        ]);
    }

    protected function postPurchase(): void
    {
        $this->transactionPending();
    }

    /**
     * Pay function
     *
     * @return $this
     * @throws RahabitPaymentException
     */
    public function ready(): self
    {
        try {
            $this->prePurchase();

            $this->purchase();

            $this->postPurchase();
        } catch (Exception $ex) {
            if ($this->getTransaction() !== null) {
                $this->transactionFailed($ex->getMessage());
            }

            if (!$ex instanceof RahabitPaymentException) {
                $ex = RahabitPaymentException::unknown($ex);
            }

            throw $ex;
        }

        return $this;
    }

    /**
     * Alias for Purchase Uri function
     *
     * @return string
     * @throws RahabitPaymentException
     */
    public function uri(): string
    {
        try {
            return $this->purchaseUri();
        } catch (RahabitPaymentException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw RahabitPaymentException::unknown($ex);
        }
    }

    /**
     * Redirect to Purchase Uri function
     *
     * @throws RahabitPaymentException
     */
    public function redirect()
    {
        try {
            return response()->redirectTo($this->purchaseUri());
        } catch (RahabitPaymentException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw RahabitPaymentException::unknown($ex);
        }
    }

    /**
     * Purchase View Params function
     *
     * @return array
     */
    protected function purchaseViewParams(): array
    {
        return [];
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function purchaseView(array $data = [])
    {
        $parameters = array_merge(
            [
                'view' => 'RahabitPayment::pages.redirect',
                'title' => null,
                'image' => null,
                'method' => 'GET',
                'form_data' => [],
            ],
            $this->purchaseViewParams(),
            $data
        );

        return response()->view($parameters['view'], array_merge(
            [
                'transaction_code' => $parameters['transaction_code'] ?? $this->getTransactionCode(),
                'bank_url' => $parameters['bank_url'] ?? $this->purchaseUri(),
            ],
            $parameters
        ));
    }

    /**
     * Alias for Purchase View function
     *
     * @param array $data
     * @return mixed
     */
    public function view(array $data = [])
    {
        return $this->purchaseView($data);
    }

    /**
     * @throws RahabitPaymentException
     */
    protected function preVerify(): void
    {
        if(!isset($this->transaction)) {
            $transaction_code_field = app('config')->get('RahabitPayment.transaction_query_param', 'tc');
            if (isset($this->request[$transaction_code_field])) {
                $this->findTransaction($this->request[$transaction_code_field]);
            } else {
                throw new TransactionNotFoundException();
            }
        }

        if ($this->transaction->status == RahabitPaymentTransaction::T_SUCCEED) {
            throw new SucceedRetryException;
        } elseif (!in_array($this->transaction->status, [
            RahabitPaymentTransaction::T_PENDING,
            RahabitPaymentTransaction::T_VERIFY_PENDING,
        ])) {
            throw InvalidRequestException::unProcessableVerify();
        }

        $this->setCurrency($this->transaction->currency);
        $this->setAmount($this->transaction->amount);

        $this->transactionVerifyPending();
    }

    protected function postVerify(): void
    {
        $this->transactionSucceed();
    }

    /**
     * Confirm function
     *
     * @param RahabitPaymentTransaction|null $transaction
     * @return self
     * @throws RahabitPaymentException
     */
    public function confirm(RahabitPaymentTransaction $transaction = null)
    {
        if(isset($transaction)) {
            $this->setTransaction($transaction);
        }

        try {
            $this->preVerify();

            $this->verify();

            $this->postVerify();
        } catch (TransactionFailedException $ex) {
            $this->transactionFailed($ex->getMessage());

            throw $ex;
        } catch (RahabitPaymentException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw RahabitPaymentException::unknown($ex);
        }

        return $this;
    }
}
