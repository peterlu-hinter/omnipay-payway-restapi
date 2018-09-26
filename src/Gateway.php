<?php

namespace Omnipay\PaywayRest;

use Omnipay\Common\AbstractGateway;
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;
use SilverStripe\Dev\Debug;

/**
 * PayWay Credit Card gateway
 */
class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'Westpac PayWay Credit Card';
    }

    public function getDefaultParameters()
    {
        return array(
            'apiKeyPublic' => '',
            'apiKeySecret' => '',
            'merchantId' => '',
            'useSecretKey' => false,
        );
    }

    /**
     * Get API publishable key
     * @return string
     */
    public function getApiKeyPublic()
    {
        return $this->getParameter('apiKeyPublic');
    }

    /**
     * Set API publishable key
     * @param  string $value API publishable key
     */
    public function setApiKeyPublic($value)
    {
        return $this->setParameter('apiKeyPublic', $value);
    }

    /**
     * Get API secret key
     * @return string
     */
    public function getApiKeySecret()
    {
        return $this->getParameter('apiKeySecret');
    }

    /**
     * Set API secret key
     * @param  string $value API secret key
     */
    public function setApiKeySecret($value)
    {
        return $this->setParameter('apiKeySecret', $value);
    }

    /**
     * Get Merchant
     * @return string Merchant ID
     */
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    /**
     * Set Merchant
     * @param  string $value Merchant ID
     */
    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * Test the PayWay gateway
     * @param  array $parameters Request parameters
     * @return \Omnipay\PaywayRest\Message\CheckNetworkRequest
     */
    public function testGateway(array $parameters = array())
    {
        return $this->createRequest(
            '\Omnipay\PaywayRest\Message\CheckNetworkRequest',
            $parameters
        );
    }

    /**
     * Purchase request
     *
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\PurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        /** @todo create customer before payment if none supplied */

        // schedule regular payment
        if (isset($parameters['frequency']) && $parameters['frequency'] !== 'once') {
            return $this->createRequest('\Omnipay\PaywayRest\Message\RegularPaymentRequest', $parameters);
        }

        // process once-off payment
        return $this->createRequest('\Omnipay\PaywayRest\Message\PurchaseRequest', $parameters);
    }

    /**
     * Create singleUseTokenId with a CreditCard
     *
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\CreateSingleUseCardTokenRequest
     */
    public function createSingleUseCardToken(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaywayRest\Message\CreateSingleUseCardTokenRequest', $parameters);
    }

    /**
     * Create singleUseTokenId with a Bank Account
     *
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\CreateSingleUseBankTokenRequest
     */
    public function createSingleUseBankToken(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaywayRest\Message\CreateSingleUseBankTokenRequest', $parameters);
    }

    /**
     * Create Customer
     *
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\CreateCustomerRequest
     */
    public function createCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaywayRest\Message\CreateCustomerRequest', $parameters);
    }

    /**
     * Update Customer contact details
     *
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\UpdateCustomerContactRequest
     */
    public function updateCustomerContact(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaywayRest\Message\UpdateCustomerContactRequest', $parameters);
    }

    /**
     * Get Customer details
     * @param  array $parameters
     * @return \Omnipay\PaywayRest\Message\CustomerDetailRequest
     */
    public function getCustomerDetails(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaywayRest\Message\CustomerDetailRequest', $parameters);

    }

    /**
     * Get Transaction details
     * @param  array $parameters
     * @return \Omnipay\PaywayRest\Message\TransactionDetailRequest
     */
    public function getTransactionDetails(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaywayRest\Message\TransactionDetailRequest', $parameters);
    }

    /**
     * Get List of Merchants
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\MerchantListRequest
     */
    public function getMerchants(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaywayRest\Message\MerchantListRequest', $parameters);
    }

    /**
     * Get List of Bank Accounts
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\BankAccountListRequest
     */
    public function getBankAccounts(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaywayRest\Message\BankAccountListRequest', $parameters);
    }

    /**
     * Get List of Transactions by receiptNumber (stored in OmniPay as transactionReference)
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\TransactionsRequest
     */
    public function getTransactions(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PaywayRest\Message\TransactionsRequest', $parameters);
    }

    /**
     * Refund (or Void) request
     * @param array $parameters
     * @return \Omnipay\PaywayRest\Message\RefundRequest
     */
    public function refund(array $parameters = array())
    {
        // note that transactionReference is not reliable, so we do an extra lookup.
        $refundParams = [
            'principalAmount' => $parameters['amount'],
            'parentTransactionId' => $parameters['transactionReference']
        ];
        $voidParams = [];
        $transactions = $this->getTransactions($parameters);
        $response = $transactions->send();
        $data = $response->getData('data');
        if ($data && isset($data[0]) && isset($data[0]['transactionId'])) {
            $refundParams['parentTransactionId'] = $data[0]['transactionId'];
            $voidParams['transactionId'] = $data[0]['transactionId'];

        }
//         note that transaction might not be refundable, so we do an extra lookup.
        $transaction = $this->getTransactionDetails(['transactionId' => $data[0]['transactionId']]);
        $response = $transaction->send();
        $canRefund = $response->getData('isRefundable');
        $canVoid = $response->getData('isVoidable');
        if ($canRefund) {
            return $this->createRequest('\Omnipay\PaywayRest\Message\RefundRequest', $refundParams);
        } elseif ($canVoid) {
            return $this->createRequest('\Omnipay\PaywayRest\Message\VoidRequest', $voidParams);
        }

    }

}
