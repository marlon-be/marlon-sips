<?php

namespace Sips\Office\CashManagement;

use \InvalidArgumentException;
use Sips\ShaComposer\ShaComposer;

class DuplicateResponse
{
    /** @var string */
    const SHASIGN_FIELD = "SEAL";

    /**
     * @var array
     * always present if valid: acquirerResponseCode, authorisationId, responseCode, transactionDateTime, seal
     */
    private $parameters;

    /**
     * @var string
     */
    private $shaSign;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
        $this->shaSign = $this->extractShaSign($parameters);
    }

    private function extractShaSign(array $parameters)
    {
        $parameters = array_change_key_case($parameters, CASE_UPPER);
        $key = strtoupper(self::SHASIGN_FIELD);

        if (empty($parameters[$key])) {
            throw new InvalidArgumentException('SHASIGN parameter not present in parameters.');
        }

        return $parameters[$key];
    }

    public function getSeal()
    {
        return $this->shaSign;
    }

    public function isValid(ShaComposer $shaComposer)
    {
        return $shaComposer->compose($this->parameters) == $this->shaSign;
    }

    public function getParam($key)
    {
        $methodName = 'get' . ucfirst($key);
        if (method_exists($this, $methodName)) {
            return $this->{$methodName}();
        }

        // always use uppercase
        $key = strtoupper($key);
        $parameters = array_change_key_case($this->parameters, CASE_UPPER);
        if (!array_key_exists($key, $parameters)) {
            throw new InvalidArgumentException('Parameter ' . $key . ' does not exist.');
        }

        return $parameters[$key];
    }

    public function isSuccessful()
    {
        return in_array($this->getParam('responseCode'), array('00', '60'));
    }

    public function toArray()
    {
        return $this->parameters;
    }
}
