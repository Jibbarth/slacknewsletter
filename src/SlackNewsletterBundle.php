<?php

namespace Barth\SlackNewsletterBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SlackNewsletterBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
