<?php

namespace AppBundle\Fixtures\CurrencySource;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\CurrencySource;

class CurrencySourceData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $cbr = new CurrencySource();
        $cbr->setName('Central Bank of Russia');
        $cbr->setFormat('xml');
        $cbr->setUrl('http://www.cbr.ru/scripts/XML_daily.asp');
        $cbr->setPriority(1);
        $cbr->setKeysMetadata(array(
            'currency_code_key' => 'CharCode',
            'currency_rate_key' => 'Value',
        ));
        $manager->persist($cbr);

        $yahoo = new CurrencySource();
        $yahoo->setName('Yahoo');
        $yahoo->setFormat('json');
        $yahoo->setUrl('https://query.yahooapis.com/v1/public/yql?q=select+*+from+yahoo.finance.xchange+where+pair+=+%22USDRUB,EURRUB%22&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=');
        $yahoo->setPriority(2);
        $yahoo->setKeysMetadata(array(
            'currency_code_key' => 'id',
            'currency_rate_key' => 'Rate',
        ));
        $manager->persist($yahoo);

        $manager->flush();
    }
}