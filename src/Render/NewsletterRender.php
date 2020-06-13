<?php

declare(strict_types=1);

namespace App\Render;

use App\Collection\SectionCollection;
use Twig\Environment;

final class NewsletterRender
{
    private Environment $twig;
    /**
     * @var array<string, string>
     */
    private array $mailTemplate;

    /**
     * @param array<string, string> $mailTemplate
     */
    public function __construct(
        Environment $twig,
        array $mailTemplate
    ) {
        $this->twig = $twig;
        $this->mailTemplate = $mailTemplate;
    }

    public function render(SectionCollection $messages): string
    {
        return $this->twig->render('newsletter.html.twig', [
            'tpl' => $this->mailTemplate,
            'messages' => $messages,
        ]);
    }
}
