<?php

namespace Sips\ShaComposer;

use Sips\Passphrase;

class OfficeShaComposer implements ShaComposer
{
    /**
     * @var string Passphrase
     */
    private $passphrase;

    private $ignoredParameters = array('keyVersion', 'seal');

    /**
     * @param \Sips\Passphrase $passphrase
     */
    public function __construct(Passphrase $passphrase)
    {
        $this->passphrase = $passphrase;
    }

    public function compose(array $parameters)
    {
        foreach ($this->ignoredParameters as $param) {
            if (isset($parameters[$param])) {
                unset($parameters[$param]);
            }
        }

        ksort($parameters);
        $seal = '';
        foreach ($parameters as $parameterValue) {
            $seal .= $parameterValue;
        }
        $seal = utf8_encode($seal);

        return hash_hmac('sha256', $seal, (string)$this->passphrase);
    }
}
