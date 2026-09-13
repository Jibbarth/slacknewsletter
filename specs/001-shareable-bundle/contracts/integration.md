# Bundle Integration Contract

**Feature**: Shareable Bundle  
**Date**: 2026-09-13

## Overview

This contract defines how consumer applications integrate the SlackNewsletter bundle into their Symfony projects.

## Composer Installation

### Local Development (Path Repository)

Consumer's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../SlackNewsletter"
    }
  ],
  "require": {
    "barth/slacknewsletter": "@dev"
  }
}
```

Command:
```bash
composer require barth/slacknewsletter
```

### Production (Packagist)

Once published to Packagist:

```bash
composer require barth/slacknewsletter
```

## Bundle Registration

**Automatic**: Symfony Flex auto-registers the bundle in `config/bundles.php`:

```php
return [
    // ... other bundles
    Barth\SlackNewsletterBundle\SlackNewsletterBundle::class => ['all' => true],
];
```

**Manual** (if Flex is disabled):

Add the line above manually to `config/bundles.php`.

## Routing Integration

Create `config/routes/slack_newsletter.yaml` in the consumer project:

```yaml
slack_newsletter:
    resource: "@SlackNewsletterBundle/config/bundle/routing.php"
    # Optional: add prefix for namespacing
    # prefix: '/newsletter'
```

## Configuration Requirements

The bundle requires the following environment variables (create `.env.local` in consumer project):

```env
SLACK_TOKEN=xoxb-your-token-here
MAILER_DSN=smtp://localhost
CHANNELS_FILE=config/channels.json
```

## Service Access

All public services are available via autowiring in the consumer project:

```php
use Barth\SlackNewsletterBundle\Builder\NewsletterBuilder;
use Barth\SlackNewsletterBundle\Service\Slack\BrowseService;

class YourController
{
    public function __construct(
        private NewsletterBuilder $builder,
        private BrowseService $browseService
    ) {}
}
```

## Commands Available

Once the bundle is installed, these commands are available:

```bash
bin/console app:newsletter:browse    # Browse Slack channels
bin/console app:newsletter:build     # Build newsletter
bin/console app:newsletter:send      # Send newsletter
```

## Asset Publishing

Assets are automatically published during `composer install`:

```bash
# Manual asset installation (if needed)
bin/console assets:install --symlink
```

## Extension Points

Consumer projects can override:

1. **Templates**: Place overrides in `templates/bundles/SlackNewsletterBundle/`
2. **Services**: Override service definitions in consumer's `config/services.yaml`
3. **Configuration**: Override parameters or service arguments

## Contract Validation

To verify the bundle is correctly installed:

1. Check bundle registration:
   ```bash
   bin/console debug:container SlackNewsletterBundle
   ```

2. List available commands:
   ```bash
   bin/console list app:newsletter
   ```

3. Verify routing:
   ```bash
   bin/console debug:router | grep slack_newsletter
   ```

## Breaking Changes Policy

- Major version bumps: Breaking changes allowed (namespace changes, removed services)
- Minor version bumps: New features, backward-compatible
- Patch version bumps: Bug fixes only

## Minimum Requirements

- PHP >= 8.4
- Symfony >= 8.0
- Composer >= 2.0
