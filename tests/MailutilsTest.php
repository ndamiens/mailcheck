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
            'nicolas.example', 'nicolas.invalid',
            'example.com', 'example.net',
            'example.org'
        ];
        foreach ($domains as $domain) {
            $excepted = false;
            try {
                MailUtils::address("nicolas@$domain", false);
            } catch (InvalidEmailException $ex) {
                $this->assertEquals(InvalidEmailException::SYNTAX, $ex->getCode());
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
        $this->assertTrue(MailUtils::hasMX(MaiLUtils::address("nicolas@damiens.info")));
        $this->assertFalse(MailUtils::hasMX(MaiLUtils::address("nicolas@mail.damiens.info", false)));
        $this->assertFalse(MailUtils::hasMX(MaiLUtils::address("nicolas@unknown.damiens.info", false)));
    }

    public function testHasA() {
        $this->assertTrue(MailUtils::hasA(MaiLUtils::address("nicolas@damiens.info")));
        $this->assertFalse(MailUtils::hasA(MaiLUtils::address("nicolas@unknown.damiens.info", false)));
    }

}
