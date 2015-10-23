<?php

namespace Sips\Office\CashManagement;

use Sips\Office\OfficeRequest;
use Sips\SipsCurrency;

class DuplicateRequest extends OfficeRequest
{
    protected function getMethod()
    {
        return '/rs-services/v2/cashManagement/duplicate';
    }

    protected function getFields()
    {
        return array(
            'amount'                        => true,
            'captureDay'                    => true,
            'captureMode'                   => true,
            'currencyCode'                  => true,
            'customerEmail'                 => false,
            'customerId'                    => false,
            'customerIpAddress'             => false,
            'deliveryAddress'               => $this->getDeliveryAddressFields(),
            'deliveryContact'               => $this->getDeliveryContactFields(),
            'deliveryData'                  => array(
                'deliveryChargeAmount'      => false,
                'deliveryMethod'            => false,
                'deliveryMode'              => false,
                'deliveryOperator'          => false,
                'estimatedDeliveryDate'     => false,
                'estimatedDeliveryDelay'    => false
            ),
            'fromMerchantId'                => true,
            'fromTransactionReference'      => true,
            'interfaceVersion'              => true,
            'keyVersion'                    => true,
            'merchantId'                    => true,
            'merchantTransactionDateTime'   => true,
            'orderChannel'                  => true,
            'orderId'                       => false,
            'returnContext'                 => true,
            's10TransactionReference'       => $this->getS10TransactionReferenceFields(),
            's10FromTransactionReference'   => array(
                's10FromTransactionId'      => false,
                's10FromTransactionIdDate'  => false
            ),
            'shoppingCartDetail'            => array(
                'shoppingCartTotalAmount'       => false,
                'shoppingCartTotalQuantity'     => false,
                'shoppingCartTotalTaxAmount'    => false,
                'mainProduct'                   => false,
                'List<shoppingCartItem>'        => array(
                    'productName'           => false,
                    'productDescription'    => false,
                    'productCode'           => false,
                    'productSKU'            => false,
                    'productUnitAmount'     => false,
                    'productQuantity'       => false,
                    'productTaxRate'        => false,
                    'productUnitTaxAmount'  => false,
                    'productCategory'       => false
                )
            ),
            'statementReference'            => false,
            'transactionOrigin'             => false,
            'transactionReference'          => true,
            'fraudData'                     => $this->getFraudDataFields()
        );
    }

    public function setTransactionReference($transactionReference)
    {
        $this->validateTransactionReference($transactionReference);
        $this->setParameter('transactionReference', $transactionReference);
    }

    public function setFromTransactionReference($fromTransactionReference)
    {
        $this->validateTransactionReference($fromTransactionReference);
        $this->setParameter('fromTransactionReference', $fromTransactionReference);
    }

    public function setCurrency($currency)
    {
        $this->validateCurrency($currency);
        $this->setParameter('currencyCode', SipsCurrency::convertCurrencyToSipsCurrencyCode($currency));
    }

    public function setAmount($amount)
    {
        $this->validateAmount($amount);
        $this->setParameter('amount', $amount);
    }

    public function setCaptureMode($captureMode)
    {
        $this->validateCaptureMode($captureMode);
        $this->setParameter('captureMode', $captureMode);
    }

    public function setInterfaceVersion($interfaceVersion)
    {
        if (!preg_match('/^CR_WS_\d+\.\d+$/', $interfaceVersion)) {
            throw new \InvalidArgumentException(sprintf('"%s" is an invalid interface version. Should be in format CR_WS_x.x'));
        }
        $this->setParameter('interfaceVersion', $interfaceVersion);
    }

    public function setMerchantTransactionDateTime(\DateTime $merchantTransactionDateTime)
    {
        $this->setParameter('merchantTransactionDateTime', $merchantTransactionDateTime->format(\DateTime::ISO8601));
    }

    public function setOrderChannel($orderChannel)
    {
        $this->validateOrderChannel($orderChannel);
        $this->setParameter('orderChannel', $orderChannel);
    }
}
