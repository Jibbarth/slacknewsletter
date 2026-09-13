<?php

declare(strict_types=1);

namespace Barth\SlackNewsletterBundle\Controller;

use Barth\SlackNewsletterBundle\Builder\NewsletterBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TestMailController
{
    #[Route('/test/mail', name: 'test_mail')]
    public function index(NewsletterBuilder $buildService): Response
    {
        $newsletter = $buildService->build();

        return new Response($newsletter, Response::HTTP_OK);
    }
}
