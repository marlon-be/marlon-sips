<?php

namespace Sips\Office;

use BadMethodCallException;
use InvalidArgumentException;
use RuntimeException;
use Sips\ShaComposer\ShaComposer;
use Sips\SipsCurrency;

abstract class OfficeRequest
{
    const TEST = 'https://office-server.test.sips-atos.com';
    const PRODUCTION = 'https://office-server.sips-atos.com';

    /** @var ShaComposer */
    private $shaComposer;

    private $sipsUri = self::TEST;

    private $parameters = array();

    public function __construct(ShaComposer $shaComposer)
    {
        $this->shaComposer = $shaComposer;
    }

    abstract protected function getMethod();
    /**
     * @return array
     * Should return an array of all allowed fields, with their 'required' flag:
     * {
     *  'fieldName': *isRequired,
     *  'compositeField': {
     *      'subField': *isRequired*
     *  }
     * }
     */
    abstract protected function getFields();

    /** @return string */
    public function getSipsUri()
    {
        return $this->sipsUri;
    }

    public function setSipsUri($sipsUri)
    {
        $this->validateUri($sipsUri . $this->getMethod());
        $this->sipsUri = $sipsUri;
    }

    /** @return string */
    public function getShaSign()
    {
        return $this->shaComposer->compose($this->toArray());
    }

    public function setParameter($name, $value)
    {
        $this->parameters[ $name ] = $value;
    }

    protected function validateUri($uri)
    {
        if(!filter_var($uri, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Uri is not valid");
        }
        if(strlen($uri) > 200) {
            throw new InvalidArgumentException("Uri is too long");
        }
    }

    protected function validateTransactionReference($transactionReference)
    {
        if(preg_match('/[^a-zA-Z0-9_-.]/', $transactionReference)) {
            throw new InvalidArgumentException("TransactionReferences cannot contain special characters");
        }
    }

    protected function validateCurrency($currency)
    {
        if(!array_key_exists(strtoupper($currency), SipsCurrency::getCurrencies())) {
            throw new InvalidArgumentException("Unknown currency");
        }
    }

    protected function validateDateTime($dateTime)
    {
        if (!\DateTime::createFromFormat('Y#m#d H#i#s', $dateTime)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid date-time, should be in format yyyy-mm-dd hh:mm:ss'));
        }
    }

    protected function validateOrderChannel($orderChannel)
    {
        $allowed = array('INTERNET', 'MOTO', 'TELEPHONE_ORDER', 'MAIL_ORDER', 'FAX', 'IVR');
        if (!in_array($orderChannel, $allowed)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid OrderChannel, should be one of: %s', $orderChannel, implode(', ', $allowed)));
        }
    }

    /**
     * Amount is in cents, eg EUR 12.34 is written as 1234
     */
    public function validateAmount($amount)
    {
        if(!is_int($amount)) {
            throw new InvalidArgumentException("Integer expected. Amount is always in cents");
        }
        if($amount <= 0) {
            throw new InvalidArgumentException("Amount must be a positive number");
        }
    }

    public function validateCaptureMode($captureMode)
    {
        $allowed = array('AUTHOR_CAPTURE', 'VALIDATION', 'IMMEDIATE');
        if (!in_array($captureMode, $allowed)) {
            throw new InvalidArgumentException(sprintf('"%s" is an invalid CaptureMode, allowed: %s', $captureMode, implode(',', $allowed)));
        }
    }

    public function toArray()
    {
        $this->validate();
        return $this->parameters;
    }

    public function validate()
    {
        foreach($this->getRequiredFields($this->getFields()) as $field => $isRequired) {
            if(empty($this->parameters[ $field ])) {
                throw new RuntimeException($field . " can not be empty");
            }
        }
    }

    private function getRequiredFields(array $fields)
    {
        $required = array();

        foreach ($fields as $field => $isRequired) {
            if (is_array($isRequired)) {
                $isRequired = $this->getRequiredFields($isRequired);
            }
            if ($isRequired) {
                $required[ $field ] = $isRequired;
            }
        }

        return $required;
    }

    protected function getS10TransactionReferenceFields()
    {
        return array(
            's10TransactionId'      => false,
            's10TransactionIdDate'  => false
        );
    }

    protected function getDeliveryAddressFields()
    {
        return array(
            'addressAdditional1'    => false,
            'addressAdditional2'    => false,
            'addressAdditional3'    => false,
            'City'                  => false,
            'company'               => false,
            'country'               => false,
            'postBox'               => false,
            'state'                 => false,
            'street'                => false,
            'streetNumber'          => false,
            'zipCode'               => false
        );
    }

    protected function getDeliveryContactFields()
    {
        return array(
            'email'     => false,
            'firstname' => false,
            'gender'    => false,
            'lastname'  => false,
            'mobile'    => false,
            'phone'     => false,
            'Title'     => false
        );
    }

    protected function getFraudDataFields()
    {
        return array(
            'allowedCardArea'           => false,
            'allowedCardCountryList'    => false,
            'allowedIpArea'             => false,
            'allowedIpCountryList'      => false,
            'bypass3DS'                 => false,
            'bypassCtrlList'            => false,
            'bypassInfoList'            => false,
            'deniedCardArea'            => false,
            'deniedCardCountryList'     => false,
            'deniedIpArea'              => false,
            'deniedIpCountryList'       => false
        );
    }

    public function __call($method, $args)
    {
        if(substr($method, 0, 3) == 'set') {
            $field = lcfirst(substr($method, 3));
            if (array_key_exists($this->getMethod(), $field)) {
                $this->parameters[$field] = $args[0];
                return;
            }
        }

        if(substr($method, 0, 3) == 'get') {
            $field = lcfirst(substr($method, 3));
            if(array_key_exists($field, $this->parameters)) {
                return $this->parameters[$field];
            }
        }

        throw new BadMethodCallException("Unknown method $method");
    }
}
