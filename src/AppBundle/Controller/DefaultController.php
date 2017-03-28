<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('AppBundle:Default:index.html.twig', $this->get('currency_parser')->getCurrencyRates());
    }

    /**
     * @Route("/update_currencies", name="update_currencies")
     */
    public function updateCurrenciesAction()
    {
        return new JsonResponse($this->get('currency_parser')->getCurrencyRates());
    }
}
