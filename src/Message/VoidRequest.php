<?php
/**
 * PaywayRest Void Request
 */

namespace Omnipay\PaywayRest\Message;

/**
 * PaywayRest Transaction Detail Request
 *
 * @link https://www.payway.com.au/rest-docs/index.html#void-a-transaction
 */
class VoidRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate(
            'transactionId'
        );

        return $data = array();
    }

    public function getEndpoint()
    {
        return $this->endpoint . '/transactions/' . $this->getTransactionId() . '/void';
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
