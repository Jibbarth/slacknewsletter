<?php

declare(strict_types=1);

namespace App\Controller;

use App\Builder\NewsletterBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class TestMailController
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
