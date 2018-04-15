<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TestMailController extends Controller
{
    /**
     * @Route("/test/mail", name="test_mail")
     */
    public function index()
    {
        return $this->render('test_mail/index.html.twig', [
            'controller_name' => 'TestMailController',
        ]);
    }
}
