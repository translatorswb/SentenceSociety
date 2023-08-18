<?php

namespace App\Tests\Service;

use App\Entity\Rating;
use App\Entity\SourcePhrase;
use App\Entity\TargetPhrase;
use App\Entity\TargetPhraseFlag;
use App\Service\ExportService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExportServiceTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;


    /** @var ExportService */
    private $exportService;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // gets the special container that allows fetching private services
        // see https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
        $container = self::$container;

        $this->exportService = $container
            ->get(ExportService::class);
    }

    function testExportEmpty()
    {
        $export = $this->exportService->export();
        $this->assertIsArray($export);
        $this->assertCount(0, $export);
    }

    function testExportData()
    {
        $sourcePhrase = new SourcePhrase();
        $sourcePhrase->setText('test1');
        $this->entityManager->persist($sourcePhrase);
        $this->entityManager->flush();

        $export = $this->exportService->export();
        $this->assertIsArray($export);
//        var_dump($export);

        // no translation -> 0
        $this->assertCount(0, $export);

        $targetPhrase = new TargetPhrase();
        $targetPhrase->setText("TRANSLATED VERSION OF test1");
        $targetPhrase->setTimestamp(new \DateTime());
        $targetPhrase->setSource($sourcePhrase);
        $this->entityManager->persist($targetPhrase);
        $this->entityManager->flush();

        $targetPhrase2 = new TargetPhrase();
        $targetPhrase2->setText("TRANSLATED VERSION OF test1 v2");
        $targetPhrase2->setTimestamp(new \DateTime());
        $targetPhrase2->setSource($sourcePhrase);
        $this->entityManager->persist($targetPhrase2);
        $this->entityManager->flush();

        $export = $this->exportService->export();
//        var_dump($export);
        $this->assertIsArray($export);
        // translation, but no validation -> 0
        $this->assertCount(0, $export);

        $rating = new Rating();
        $rating->setTimestamp(new \DateTime());
        $rating->setValue(1);
        $rating->setTarget($targetPhrase);
        $this->entityManager->persist($rating);
        $this->entityManager->flush();

        $rating = new Rating();
        $rating->setTimestamp(new \DateTime());
        $rating->setValue(1);
        $rating->setTarget($targetPhrase2);
        $this->entityManager->persist($rating);
        $this->entityManager->flush();

        $rating = new Rating();
        $rating->setTimestamp(new \DateTime());
        $rating->setValue(2);
        $rating->setTarget($targetPhrase2);
        $this->entityManager->persist($rating);
        $this->entityManager->flush();

        $rating = new Rating();
        $rating->setTimestamp(new \DateTime());
        $rating->setValue(1);
        $rating->setTarget($targetPhrase2);
        $this->entityManager->persist($rating);
        $this->entityManager->flush();

        //
        //
        //
        $flag = new TargetPhraseFlag();
        $flag->setTimestamp(new \DateTime());
        $flag->setTargetPhrase($targetPhrase);
        $this->entityManager->persist($flag);
        $this->entityManager->flush();

        $export = $this->exportService->export();
//        var_dump($export);
        $this->assertIsArray($export);
        // translation, with 1 rating -> 1
        $this->assertCount(2, $export);
        // 6 fields in the export
        $this->assertCount(6, $export[0]);
//        $this->assertEquals(1, $export[0]['source_id']);
//        $this->assertEquals(1, $export[0]['target_id']);
        $this->assertEquals("test1", $export[0]['source_text']);
        $this->assertEquals("TRANSLATED VERSION OF test1", $export[0]['target_text']);
        $this->assertEquals('[1]', $export[0]['ratings']);
        $this->assertEquals(1, $export[0]['flags']);


        $this->assertEquals('[1,2,1]', $export[1]['ratings']);
    }

}
