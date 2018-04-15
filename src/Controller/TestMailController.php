<?php

namespace App\Controller;

use App\Service\Newsletter\BuildService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class TestMailController
 * @package App\Controller
 */
class TestMailController extends Controller
{
    /**
     * @Route("/test/mail", name="test_mail")
     * @param BuildService $buildService
     * @return Response
     */
    public function index(BuildService $buildService)
    {
        $newsletter = $buildService->build();

        return new Response($newsletter, Response::HTTP_OK);
    }
}
