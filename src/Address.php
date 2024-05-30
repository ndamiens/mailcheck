<?php

namespace ND\MailUtils;

class Address
{

    private string $localPart;

    private string $domain;

    /**
     *  @return $this
     */
    public function setLocalPart(string $localPart): self
    {
        $this->localPart = $localPart;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocalPart(): string
    {
        return $this->localPart;
    }

    /**
     * @param string $domain
     * @return $this
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->localPart . "@" . $this->domain;
    }

    public function getGDPRAddress(): string
    {
        $len = mb_strlen($this->localPart);
        if ($len <= 2) {
            return "******";
        } elseif ($len <= 4) {
            return substr($this->localPart, 0, 2) . "****";
        }
        return substr($this->localPart, 0, 2) . str_repeat("*", $len - 4) . substr($this->localPart, -2, 2) . '@' . $this->domain;
    }
}
