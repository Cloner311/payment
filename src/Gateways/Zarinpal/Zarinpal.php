<?php
/**
 * Api Version: v1
 * Api Document Date: 1401/09/14
 * Last Update: 1401/09/14
 */

namespace Rahabit\Payment\Gateways\Zarinpal;

use Rahabit\Payment\Gateways\AbstractGateway;
use Rahabit\Payment\Gateways\GatewayInterface;


use Rahabit\Payment\Exceptions\GatewayException;
use Rahabit\Payment\Exceptions\InvalidDataException;
use Rahabit\Payment\Exceptions\RahabitPaymentException;
use Rahabit\Payment\Exceptions\TransactionFailedException;

use Rahabit\Payment\Helpers\Currency;

use Exception;
use SoapFault;
use SoapClient;

class Zarinpal extends AbstractGateway implements GatewayInterface
{
//    private const WSDL_URL       = "https://www.zarinpal.com/pg/services/WebGate/wsdl";
//    private const WEB_GATE_URL   = "https://www.zarinpal.com/pg/StartPay/{Authority}";
//    private const ZARIN_GATE_URL = "https://www.zarinpal.com/pg/StartPay/{Authority}/ZarinGate";
//    private const SANDBOX_URL    = "https://sandbox.zarinpal.com/pg/StartPay/{Authority}";
//    public const CURRENCY        = Currency::IRT;


    private const WSDL_URL       = "https://www.zarinpal.com/pg/services/WebGate/wsdl";
    private const WEB_GATE_URL   = "https://www.zarinpal.com/pg/StartPay/{Authority}";
    private const ZARIN_GATE_URL = "https://www.zarinpal.com/pg/StartPay/{Authority}/ZarinGate";
    private const SANDBOX_URL    = "https://sandbox.zarinpal.com/pg/StartPay/{Authority}";
    public const CURRENCY        = Currency::IRT;

    /**
     * Merchant ID variable
     *
     * @var string|null
     */
    protected ?string $merchant_id;

    /**
     * Authority variable
     *
     * @var string|null
     */
    protected ?string $authority;

    /**
     * Ref Id variable
     *
     * @var string|null
     */
    protected ?string $ref_id;

    /**
     * Sandbox mod variable
     *
     * @var string
     */
    protected string $type = 'normal';

    /**
     * Add Fees variable
     *
     * @var bool
     */
    private bool $add_fees = false;

    /**
     * Gateway Name function
     *
     * @return string
     */
    public function getName(): string
    {
        return 'zarinpal';
    }

    /**
     * Set Merchant ID function
     *
     * @param string $merchant_id
     * @return $this
     */
    public function setMerchantId(string $merchant_id): self
    {
        $this->merchant_id = $merchant_id;

        return $this;
    }

    /**
     * Get Merchant ID function
     *
     * @return string|null
     */
    public function getMerchantId(): ?string
    {
        return $this->merchant_id;
    }

    /**
     * Set Type function
     *
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get Type function
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set Authority function
     *
     * @param string $authority
     * @return $this
     */
    public function setAuthority(string $authority): self
    {
        $this->authority = $authority;

        return $this;
    }

    /**
     * Get Authority function
     *
     * @return string|null
     */
    public function getAuthority(): ?string
    {
        return $this->authority;
    }

    /**
     * Set Ref ID function
     *
     * @param string $ref_id
     * @return $this
     */
    public function setRefId(string $ref_id): self
    {
        $this->ref_id = $ref_id;

        return $this;
    }

    /**
     * Get Ref ID function
     *
     * @return string|null
     */
    public function getRefId(): ?string
    {
        return $this->ref_id;
    }

    /**
     * Set Add Fees function
     *
     * @param bool $add_fees
     * @return $this
     */
    public function setAddFees(bool $add_fees): self
    {
        $this->add_fees = $add_fees;

        return $this;
    }

    /**
     * Get Token function
     *
     * @return bool
     */
    public function getAddFees(): bool
    {
        return $this->add_fees;
    }

    /**
     * Initialize function
     *
     * @param array $parameters
     * @return $this
     * @throws InvalidDataException
     */
    public function initialize(array $parameters = []): self
    {
        parent::initialize($parameters);

        $this->setGatewayCurrency(self::CURRENCY);

        $this->setMerchantId($parameters['merchant_id'] ?? app('config')->get('RahabitPayment.zarinpal.merchant-id'));

        $this->setType($parameters['type'] ?? app('config')->get('RahabitPayment.zarinpal.type', 'normal'));

        $this->setAddFees($parameters['add_fees'] ?? app('config')->get('RahabitPayment.zarinpal.add_fees', false));

        $this->setDescription($parameters['description'] ?? app('config')->get('RahabitPayment.zarinpal.description', 'تراكنش خرید'));

        $this->setCallbackUrl($parameters['callback_url']
            ?? app('config')->get('RahabitPayment.zarinpal.callback-url')
            ?? app('config')->get('RahabitPayment.callback-url')
        );

        return $this;
    }

    public function preparedAmount(): int
    {
        $amount = parent::preparedAmount();

        if ($this->getAddFees()) {
            $amount = $this->feeCalculator($amount);
        }

        return $amount;
    }

    /**
     * @throws GatewayException
     * @throws TransactionFailedException
     */
    public function purchase(): void
    {
        $fields = [
            'MerchantID' => $this->getMerchantId(),
            'Amount' => $this->preparedAmount(),
            'Description' => $this->getDescription(),
            'Email' => $this->getEmail(),
            'Mobile' => $this->getMobile(),
            'CallbackURL' => $this->preparedCallbackUrl(),
        ];

        try {

            $soap = new SoapClient(self::WSDL_URL, [
                'encoding' => 'UTF-8',
                'trace' => 1,
                'exceptions' => 1,
                'connection_timeout' => $this->getGatewayRequestOptions()['connection_timeout'] ?? 60,
            ]);

            $result = $soap->PaymentRequest($fields);
        } catch(SoapFault|Exception $ex) {
            throw GatewayException::connectionProblem($ex);
        }

        if (!isset($result->Status)) {
            throw GatewayException::unknownResponse(json_encode($result));
        }

        if ($result->Status !== 100) {
            throw ZarinpalException::error($result->Status);
        }

        if (!isset($result->Authority)) {
            throw GatewayException::unknownResponse(json_encode($result));
        }

        $this->setAuthority($result->Authority);
    }

    protected function postPurchase(): void
    {
        $this->transactionUpdate([
            'reference_number' => $this->getAuthority(),
        ]);

        parent::postPurchase();
    }

    /**
     * Purchase Uri function
     *
     * @return string
     * @throws InvalidDataException
     */
    public function purchaseUri(): string
    {
        switch ($this->getType()) {
            case 'normal':
                $url = self::WEB_GATE_URL;
                break;
            case 'zaringate':
            case 'zarin-gate':
            case 'zarin_gate':
                $url = self::ZARIN_GATE_URL;
                break;
            case 'sandbox':
                $url = self::SANDBOX_URL;
                break;
            default:
                throw new InvalidDataException('نوع گیت وارد شده نامعتبر است.');
        }

        return str_replace('{Authority}', $this->getReferenceNumber(), $url);
    }

    /**
     * Purchase View Params function
     *
     * @return array
     */
    protected function purchaseViewParams(): array
    {
        return [
            'title' => 'زرین‌پال',
            'image' => 'https://raw.githubusercontent.com/dena-a/iran-payment/master/resources/assets/img/zp.png',
        ];
    }

    /**
     * @throws RahabitPaymentException
     */
    public function preVerify(): void
    {
        parent::preVerify();

        if (isset($this->request['Status']) && $this->request['Status'] != 'OK') {
            throw ZarinpalException::error(-22);
        }

        if (isset($this->request['Authority']) && $this->request['Authority'] !== $this->getReferenceNumber()) {
            throw ZarinpalException::error(-11);
        }

        $this->setAuthority($this->getReferenceNumber());
    }

    /**
     * @throws GatewayException
     * @throws ZarinpalException
     * @throws TransactionFailedException
     */
    public function verify(): void
    {
        $fields = [
            'MerchantID' => $this->getMerchantId(),
            'Authority' => $this->getAuthority(),
            'Amount' => $this->preparedAmount(),
        ];

        try {
            $soap = new SoapClient(self::WSDL_URL, [
                'encoding' => 'UTF-8',
                'trace' => 1,
                'exceptions' => 1,
                'connection_timeout' => $this->getGatewayRequestOptions()['connection_timeout'] ?? 60,
            ]);

            $result = $soap->PaymentVerification($fields);
        } catch(SoapFault|Exception $ex) {
            throw GatewayException::connectionProblem($ex);
        }

        if (!isset($result->Status)) {
            throw GatewayException::unknownResponse(json_encode($result));
        }

        if ($result->Status !== 100 && $result->Status !== 101) {
            throw ZarinpalException::error($result->Status);
        }

        if (!isset($result->RefID)) {
            throw GatewayException::unknownResponse(json_encode($result));
        }

        $this->setRefId($result->RefID);
    }

    protected function postVerify(): void
    {
        $this->transactionUpdate([
            'tracking_code' => $this->getRefId(),
        ]);

        parent::postVerify();
    }

    private function feeCalculator(int $amount): int
    {
        $fees = $amount * 1 / 100;
        $amount += $fees;
        return intval($amount);
    }
}
