<?php

namespace App\Controller;

use App\Builder\NewsletterBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestMailController extends Controller
{
    /**
     * @Route("/test/mail", name="test_mail")
     *
     * @param NewsletterBuilder $buildService
     *
     * @return Response
     */
    public function index(NewsletterBuilder $buildService): Response
    {
        $newsletter = $buildService->build();

        return new Response($newsletter, Response::HTTP_OK);
    }
}
