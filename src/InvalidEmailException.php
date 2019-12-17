<?php

namespace ND\MailUtils;

class InvalidEmailException extends \Exception {
    const SYNTAX = 10;
    const DNSRECORDS = 20;
}
