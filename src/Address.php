<?php

namespace ND\MailUtils;

class Address {

    private $localPart;
    private $domain;

    public function setLocalPart($localPart) {
        $this->localPart = $localPart;
        return $this;
    }

    public function getLocalPart() {
        return $this->localPart;
    }

    public function setDomain($domain) {
        $this->domain = $domain;
        return $this;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function getAddress() {
        return $this->localPart . "@" . $this->domain;
    }

}
