<?php

namespace ND\MailUtils;

class MailUtils
{
    public static function normalize(string $address): string
    {
        return trim(strtolower($address));
    }

    /**
     * Check if the address is valid, raise an exception if not.
     * 
     * @param string $original_address_string
     * @param bool $doDnsChecks If true, the address will be checked if it has DNS records to receive emails.
     * @param bool $checkDisposable If true, the address will be checked if it is disposable.
     * @param bool $useIndexCache If true, the index cache will be used (built on first use) to check if the address is disposable.
     * @return Address
     * @throws InvalidEmailException
     */
    public static function address(string $original_address_string, bool $doDnsChecks = true, bool $checkDisposable = true, bool $useIndexCache = true): Address
    {
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
        if ($checkDisposable) {
            if (self::isDisposable($address, $useIndexCache)) {
                throw new InvalidEmailException("disposable address", InvalidEmailException::DISPOSABLE);
            }
        }
        return $address;
    }

    public static function hasMX(Address $address): bool
    {
        return checkdnsrr($address->getDomain(), "MX");
    }

    public static function hasA(Address $address): bool
    {
        return checkdnsrr($address->getDomain(), "A");
    }

    /**
     * Build the index for the disposable email block list
     * @param string $filename
     * @param bool $cache
     * @return array<string,array<string,string>|null>
     * @throws \Exception
     */
    private static function buildIndex(string $filename, bool $cache = true): ?array
    {
        $cacheFilename = sys_get_temp_dir() . "/mailcheck_index_" . md5($filename) . ".bin";
        if (file_exists($cacheFilename) && $cache) {
            return unserialize(file_get_contents($cacheFilename));
        }
        $tree = [];
        $f = fopen($filename, "r");
        if ($f === false) {
            return null;
        }
        while (($line = fgets($f)) !== false) {
            $treeItem = &$tree;
            foreach (str_split($line) as $char) {
                if ($char === "\n") {
                    break;
                }
                if ($char === "0") {
                    $char = "00";
                }
                if (!is_array($treeItem)) {
                    throw new \Exception("Invalid tree item");
                }
                if ((!array_search($char, array_keys($treeItem)) === false)) {
                    $treeItem["$char"] = [];
                    $treeItem = &$treeItem["$char"];
                } else {
                    $treeItem = &$treeItem["$char"];
                }
            }
        }
        if ($cache) {
            file_put_contents($cacheFilename, serialize($tree));
        }
        return $tree;
    }

    private static function searchIndex(string $indexFilename, string $domain, bool $cache = true): ?bool
    {
        $wt = self::buildIndex($indexFilename, $cache);
        if ($wt === null) {
            return null;
        }
        foreach (str_split($domain) as $char) {
            if ($char === "0") {
                $char = "00";
            }
            if (!isset($wt["$char"])) {
                return false;
            }
            $wt = $wt[$char];
        }
        return empty(array_keys($wt));
    }

    /**
     * Check if address is disposable
     * 
     * Domains list comes from 
     *  https://github.com/disposable-email-domains/disposable-email-domains
     *  https://github.com/FGRibreau/mailchecker/blob/master/list.txt
     * 
     * @param Address $address
     * @param bool $cache
     * @return bool|null
     */
    public static function isDisposable(Address $address, bool $cache = true): ?bool
    {
        return self::searchIndex(__DIR__ . "/disposable_email_blocklist.conf", $address->getDomain(), $cache);
    }
}
