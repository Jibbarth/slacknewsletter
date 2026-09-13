<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$slackToken', '%slack_newsletter.slack_token%')
        ->bind('$blocklistUrls', '%slack_newsletter.blocklist_urls%')
        ->bind('$daysToBrowse', '%slack_newsletter.days_to_browse%')
        ->bind('$publicDir', '%kernel.project_dir%/public/')
        ->bind('$mailTemplate', '%slack_newsletter.mail_template%')
        ->bind('$newsReceivers', '%slack_newsletter.news_receivers%')
        ->bind('$mailSender', '%slack_newsletter.mail_sender%')
        ->bind('$channelsData', '%slack_newsletter.channels%');

    $services->load('Barth\\SlackNewsletterBundle\\', '../../src/*')
        ->exclude([
            '../../src/DependencyInjection/',
            '../../src/Entity/',
            '../../src/SlackNewsletterBundle.php',
        ]);

    $services->load('Barth\\SlackNewsletterBundle\\Controller\\', '../../src/Controller/')
        ->tag('controller.service_arguments');
};
