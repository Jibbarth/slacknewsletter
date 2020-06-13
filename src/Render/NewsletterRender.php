<?php

declare(strict_types=1);

namespace App\Render;

use Twig\Environment;

final class NewsletterRender
{
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var array
     */
    private $mailTemplate;

    public function __construct(
        Environment $twig,
        array $mailTemplate
    ) {
        $this->twig = $twig;
        $this->mailTemplate = $mailTemplate;
    }

    public function render(array $messages): string
    {
        return $this->twig->render('newsletter.html.twig', [
            'tpl' => $this->mailTemplate,
            'messages' => $messages,
        ]);
    }
}
