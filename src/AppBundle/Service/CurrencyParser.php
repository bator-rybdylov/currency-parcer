<?php

namespace AppBundle\Service;

use AppBundle\Entity\CurrencySource;
use Doctrine\ORM\EntityManager;

class CurrencyParser
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Get currency rates
     *
     * @return array
     */
    public function getCurrencyRates()
    {
        // Currencies that will be displayed
        $currency_codes = array('USD', 'EUR');

        $currency_rates = array();

        $currency_sources_list = $this->em->getRepository('AppBundle:CurrencySource')
            ->findBy(
                array(),
                array('priority' => 'ASC')
            );

        /** @var CurrencySource $source */
        foreach ($currency_sources_list as $source) {
            // Send request to API
            $response = $this->sendRequest($source->getUrl());

            if (200 == $response['status']) {
                // Make array from response
                $content_arr = $this->toArray($response['content'], $source->getFormat());

                $keys_metadata = $source->getKeysMetadata();
                $use_next_source = false;

                // Find currency rates from response for chosen currencies
                foreach ($currency_codes as $code) {
                    $currency_rate = $this->findCurrencyRateInArray(
                        $content_arr,
                        $keys_metadata['currency_code_key'],
                        $code,
                        $keys_metadata['currency_rate_key']
                    );

                    // If currency rate is not found in response, use next API
                    if (false === $currency_rate) {
                        $use_next_source = true;
                        break;
                    } else {
                        $currency_rates[$code] = $currency_rate;
                    }
                }

                if (!$use_next_source) {
                    return array(
                        'currency_source_name' => $source->getName(),
                        'currency_rates' => $currency_rates,
                    );
                }
            }
        }

        return array(
            'currency_source_name' => null,
            'currency_rates' => array(),
        );
    }

    /**
     * Find currency rate in a given array recursively
     *
     * @param array $array_for_search
     * @param string $currency_code_key Key that is searched in array
     * @param string $currency_code Currency code
     * @param string $currency_rate_key Key that points on necessary currency rate
     * @return bool|string
     */
    private function findCurrencyRateInArray($array_for_search, $currency_code_key, $currency_code, $currency_rate_key)
    {
        if (array_key_exists($currency_code_key, $array_for_search)
            && !is_array($array_for_search[$currency_code_key])
            && false !== strpos($array_for_search[$currency_code_key], $currency_code))
        {
            return $array_for_search[$currency_rate_key];
        }

        foreach ($array_for_search as $element) {
            if (is_array($element)) {
                $currency_rate = $this->findCurrencyRateInArray($element, $currency_code_key, $currency_code, $currency_rate_key);
                if (false !== $currency_rate) {
                    return $currency_rate;
                }
            }
        }

        return false;
    }

    /**
     * Convert string to array
     *
     * @param string $content String with JSON or XML
     * @param string $content_format Format of $content ('json' or 'xml')
     * @return array|mixed
     */
    public function toArray($content, $content_format)
    {
        switch ($content_format) {
            case 'json':
                $content = json_decode($content, true);
                break;
            case 'xml':
                $content = json_decode(json_encode((array) simplexml_load_string($content)), true);
                break;
        }

        return $content;
    }

    /**
     * Send request to API to get currency info
     *
     * @param string $url URL of API
     * @return mixed
     */
    public function sendRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $curl_info = curl_getinfo($ch);

        return array(
            'status' => $curl_info['http_code'],
            'content' => $response
        );
    }
}