<?php

declare(strict_types=1);

namespace Barth\SlackNewsletterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SlackNewsletterExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Register config as parameters
        $container->setParameter('slack_newsletter.slack_token', $config['slack_token']);
        $container->setParameter('slack_newsletter.blocklist_urls', $config['blocklist_urls']);
        $container->setParameter('slack_newsletter.days_to_browse', $config['days_to_browse']);
        $container->setParameter('slack_newsletter.mail_sender', $config['mail_sender']);
        $container->setParameter('slack_newsletter.news_receivers', $config['news_receivers']);
        $container->setParameter('slack_newsletter.mail_template', $config['mail_template']);
        $container->setParameter('slack_newsletter.channels', $config['channels']);

        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2) . '/config/bundle'));
        $loader->load('services.php');
    }
}
