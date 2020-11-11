<?php

namespace ND\MailUtils;

class Address {

    /** @var string */
    private $localPart;

    /** @var string */
    private $domain;

    /**
     *  @return $this
     */
    public function setLocalPart(string $localPart) {
        $this->localPart = $localPart;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocalPart() {
        return $this->localPart;
    }

    /**
     * @param string $domain
     * @return $this
     */
    public function setDomain(string $domain) {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain() {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getAddress() {
        return $this->localPart . "@" . $this->domain;
    }

}
