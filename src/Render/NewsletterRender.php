<?php

namespace App\Render;

use Twig\Environment;

class NewsletterRender
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
        $html = $this->twig->render('newsletter.html.twig', [
            'tpl' => $this->mailTemplate,
            'messages' => $messages,
        ]);

        return $html;
    }
}