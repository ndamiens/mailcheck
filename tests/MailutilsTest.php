<?php

use PHPUnit\Framework\TestCase;
use ND\MailUtils\MailUtils;
use ND\MailUtils\InvalidEmailException;

class MailutilsTest extends TestCase {
    public function testCreateInstance() {
        $instance = MailUtils::address("nicolas@damiens.info");
        $this->assertInstanceOf(\ND\MailUtils\Address::class, $instance);
    }

    public function testWithouDomain() {
        $this->expectExceptionCode(InvalidEmailException::SYNTAX);
        MailUtils::address("nicolas");
    }

    public function testWithDomainWithoutDot() {
        $this->expectExceptionCode(InvalidEmailException::SYNTAX);
        MailUtils::address("nicolas@damiens");
    }

    public function testInvalidDomains() {
        $domains = [
            'nicolas.example' => InvalidEmailException::SYNTAX,
            'nicolas.invalid' => InvalidEmailException::SYNTAX,
            'example.com' => InvalidEmailException::SYNTAX,
            'example.net' => InvalidEmailException::SYNTAX,
            'example.org' => InvalidEmailException::SYNTAX,
            'coffeejadore.com' => InvalidEmailException::DISPOSABLE,
            'caramail.pro' => InvalidEmailException::DISPOSABLE,
            'unknown.damiens.info' => InvalidEmailException::DNSRECORDS,
            'no-mx-no-a.damiens.info' => InvalidEmailException::DNSRECORDS,
        ];
        foreach ($domains as $domain => $expectedExceptionCode) {
            $excepted = false;
            try {
                MailUtils::address("nicolas@$domain", doDnsChecks: true, checkDisposable: true);
            } catch (InvalidEmailException $ex) {
                $this->assertEquals($expectedExceptionCode, $ex->getCode());
                $excepted = true;
            }
            $this->assertTrue($excepted, "nicolas@$domain didn't raise exception", "domain $domain");
        }
    }

    public function testWithQuotes() {
        $this->expectExceptionCode(InvalidEmailException::SYNTAX);
        MailUtils::address("\"nicolas\"@damiens.info");
    }

    public function testNormalize() {
        $this->assertEquals("nicolas@damiens.info", MailUtils::address("  NicOlAs@Damiens.Info  ")->getAddress());
    }

    public function testHasMx() {
        $this->assertTrue(MailUtils::hasMX(MailUtils::address("nicolas@damiens.info")));
        $this->assertFalse(MailUtils::hasMX(MailUtils::address("nicolas@mail.damiens.info", false)));
        $this->assertFalse(MailUtils::hasMX(MailUtils::address("nicolas@unknown.damiens.info", false)));
    }

    public function testHasA() {
        $this->assertTrue(MailUtils::hasA(MailUtils::address("nicolas@damiens.info")));
        $this->assertFalse(MailUtils::hasA(MailUtils::address("nicolas@unknown.damiens.info", false)));
    }

    public function testMxOnly() {
        $this->assertTrue(MailUtils::hasMX(MailUtils::address("plop@ida.upmc.fr")));
    }

    public function testGDPR() {
        $addr = MailUtils::address("nicolas@damiens.info");
        $this->assertEquals("ni***as@damiens.info", $addr->getGDPRAddress());
    }

}
