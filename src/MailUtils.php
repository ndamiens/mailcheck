<?php

namespace ND\MailUtils;

class MailUtils {

    public static function normalize(string $address) {
        return trim(strtolower($address));
    }

    public static function address(string $original_address_string, $doDnsChecks = true) {
        $address_string = self::normalize($original_address_string);
        if (filter_var($address_string, FILTER_VALIDATE_EMAIL) !== $address_string) {
            throw new InvalidEmailException("syntax", InvalidEmailException::SYNTAX);
        }

        $address = new Address();
        list($localPart, $domain) = explode("@", $address_string);
        $address
            ->setLocalPart($localPart)
            ->setDomain($domain);
        // check invalid domains
        if (preg_match("/(\.example|\.invalid|example\.(com|net|org))$/", $address->getDomain())) {
            throw new InvalidEmailException("invalid domains", InvalidEmailException::SYNTAX);
        }

        // no quotes
        if (strpos($address->getLocalPart(), "\"") !== false) {
            throw new InvalidEmailException("no quotes", InvalidEmailException::SYNTAX);
        }

        if ($doDnsChecks) {
            // has dns records
            if (!(self::hasMX($address) || self::hasA($address))) {
                throw new InvalidEmailException("no dns record for domain", InvalidEmailException::DNSRECORDS);
            }
        }
        return $address;
    }

    public static function hasMX(Address $address) {
        return checkdnsrr($address->getDomain(), "MX");
    }

    public static function hasA(Address $address) {
        return checkdnsrr($address->getDomain(), "A");
    }

}
