<?php
/**
 * PaywayRest Transactions Request
 */
namespace Omnipay\PaywayRest\Message;

/**
 * PaywayRest Transactions Request
 *
 * @link https://www.payway.com.au/rest-docs/index.html#search-transactions
 */
class TransactionsRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate(
            'transactionReference'
        );

        return array();
    }

    public function getEndpoint()
    {
        return $this->endpoint . '/transactions/search-receipt?receiptNumber=' . $this->getTransactionReference();
    }

    public function getHttpMethod()
    {
        return 'GET';
    }

    public function getUseSecretKey()
    {
        return true;
    }
}
