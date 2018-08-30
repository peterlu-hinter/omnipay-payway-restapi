<?php
/**
 * PaywayRest Refund Request
 */

namespace Omnipay\PaywayRest\Message;

/**
 * PaywayRest Transaction Detail Request
 *
 * @link https://www.payway.com.au/rest-docs/index.html#refund-a-payment
 */
class RefundRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate(
            'parentTransactionId',
            'principalAmount'
        );

        $data = array(
            'transactionType' => 'refund',
            'parentTransactionId' => $this->getParentTransactionId(),
            'principalAmount' => $this->getPrincipalAmount(),
        );

        if ($this->getOrderNumber()) {
            $data['orderNumber'] = $this->getOrderNumber();
        }
        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint . '/transactions';
    }

    public function getHttpMethod()
    {
        return 'POST';
    }

    public function getUseSecretKey()
    {
        return true;
    }
}
