<?php

namespace App\Tests\model;


use App\Model\TranslationSession;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TranslationSessionTest extends KernelTestCase
{
    public function testSerialize() {
        $original = new TranslationSession();
        $original->setUserId(12);
        $serialized = serialize($original);
        // uncomment statement below to get serialized version of the current version
//        echo $serialized;
        $this->assertNotNull($serialized);
        /** @var TranslationSession $restored */
        $restored = unserialize($serialized);
        $this->assertNotNull($restored);
        $this->assertEquals(12, $restored->getUserId());
    }

    public function testDeSerializeV1() {
        $serialized_v1 = 'C:28:"App\Model\TranslationSession":82:{a:8:{i:0;i:1;i:1;i:0;i:2;a:0:{}i:3;a:0:{}i:4;a:0:{}i:5;a:0:{}i:6;a:0:{}i:7;a:0:{}}}';
        $restored = unserialize($serialized_v1);
        $this->assertNotNull($restored);
    }

    public function testDeSerializeV2() {
        $serialized_v2 = 'C:28:"App\Model\TranslationSession":92:{a:9:{i:0;i:2;i:1;i:0;i:2;a:0:{}i:3;a:0:{}i:4;a:0:{}i:5;a:0:{}i:6;a:0:{}i:7;a:0:{}i:8;a:0:{}}}';
        $restored = unserialize($serialized_v2);
        $this->assertNotNull($restored);
    }

    public function testDeSerializeV3() {
        $serialized_v3 = 'C:28:"App\Model\TranslationSession":101:{a:10:{i:0;i:3;i:1;i:0;i:2;a:0:{}i:3;a:0:{}i:4;a:0:{}i:5;a:0:{}i:6;a:0:{}i:7;a:0:{}i:8;a:0:{}i:9;i:2;}}';
        /** @var TranslationSession $restored */
        $restored = unserialize($serialized_v3);
        $this->assertNotNull($restored);
        $this->assertEquals(2, $restored->getUserLevel());
    }


}