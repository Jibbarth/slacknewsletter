<p align="center">
    <img src="https://repository-images.githubusercontent.com/129633240/a2815d80-adbe-11ea-9379-b3b04705d572" alt="Slack Newsletter Preview">
</p>

# Slack Newsletter

![CI](https://github.com/Jibbarth/slacknewsletter/workflows/CI/badge.svg?branch=master)
![Version](https://img.shields.io/packagist/v/barth/slacknewsletter)

This application allows you to generate a newsletter from your [Slack](https://slack.com) channels.
It goes through the channels looking for links, combines them into a html file, and sends it by email.
It's ideal for keeping track of your finds when the historical Slack reaches its limit.

## Installation

Install the bundle via Composer:

```bash
composer require barth/slacknewsletter
```

If Symfony Flex is not enabled, register the bundle manually in `config/bundles.php`:

```php
return [
    // ...
    Barth\SlackNewsletterBundle\SlackNewsletterBundle::class => ['all' => true],
];
```

## Configuration

Create `config/packages/slack_newsletter.yaml`:

```yaml
slack_newsletter:
    slack_token: '%env(SLACK_TOKEN)%'
    blocklist_urls: []
    days_to_browse: 7
    mail_sender: 'newsletter@example.com'
    news_receivers:
        - 'me@example.net'
        - 'team@example.net'
    channels:
        - { name: 'general', link: 'CXXXXXXX', description: 'General discussions' }
        - { name: 'tech', link: 'CYYYYYYYY', description: 'Technical topics', image: 'https://example.com/tech.png' }
    mail_template:
        main_color: '#333'
        background_color: '#f7f7f7'
        company_name: 'Your Company'
        base_slack_url: 'https://yourworkspace.slack.com'
```

**Configuration Keys:**
- `slack_token`: Slack API token (required). Generate one at [Slack Apps](https://github.com/Jibbarth/slacknewsletter/wiki/Generate-an-App-to-get-a-Token).
- `blocklist_urls`: Array of URL patterns to exclude (optional).
- `days_to_browse`: Days of history to retrieve (default: 7).
- `mail_sender`: From address for emails (required).
- `news_receivers`: Array of recipient emails (required).
- `channels`: Array of Slack channels with `name`, `link` (channel ID), `description`, and optional `image`.
- `mail_template`: Template customization (all fields optional).

**Migrating from `channels.json`?** Use the environment preprocessor:

```yaml
slack_newsletter:
    channels: '%env(json:file:resolve:CHANNELS_FILE)%'
```

And in `.env`:
```
CHANNELS_FILE=config/channels.json
```

Set environment variables in `.env.local`:

```
MAILER_DSN=smtp://awesome-smtp:25
SLACK_TOKEN=xoxp-XXXXXXXXX-XXXXXXX-XXXXXXXXX
```

## Usage

The bundle provides three commands:

**1. Browse channels and store messages:**
```bash
php bin/console app:newsletter:browse
```
Options:
- `-d, --days=DAYS`: Override `days_to_browse` config (e.g., `-d 5`).

**2. Build the newsletter HTML:**
```bash
php bin/console app:newsletter:build
```
Options:
- `--no-archive`: Keep messages after building (don't move to archive).

**3. Send the newsletter via email:**
```bash
php bin/console app:newsletter:send
```
Options:
- `--no-archive`: Keep newsletter after sending (don't move to archive).

**Cron example:**
```bash
# Every day at 8am, browse channels
0 8 * * * php bin/console app:newsletter:browse
# Every Monday at 8:05am, build and send
5 8 * * 1 php bin/console app:newsletter:build && php bin/console app:newsletter:send
```

## Testing

No automated test suite yet. Manual testing:

1. Browse channels: `php bin/console app:newsletter:browse -d 5`
2. Build newsletter: `php bin/console app:newsletter:build --no-archive`
3. View in browser: `symfony server:start -d` → [http://127.0.0.1:8000/test/mail](http://127.0.0.1:8000/test/mail)
4. Send test email: `php bin/console app:newsletter:send --no-archive`

The `--no-archive` flag prevents archiving for repeated testing.

## Customization

Override templates by copying from `vendor/barth/slacknewsletter/templates/` to your project's `templates/bundles/SlackNewsletterBundle/`.

## Contributing

Contributions welcome. Fork, branch, PR. Include context in your PR description.

## License

MIT License. See `composer.json` or [LICENSE](LICENSE) file.

## Built With

* [Symfony 8.0](http://symfony.com/)
* [FlySystem](http://flysystem.thephpleague.com/)
* [jolicode/slack-php-api](https://github.com/jolicode/slack-php-api)
* [Embed](https://github.com/oscarotero/Embed)
* [Carbon](https://carbon.nesbot.com/)

## Versioning

- **Major**: breaking changes allowed.
- **Minor**: backward‑compatible changes only.
- **Patch**: bug fixes only.

**Minimum Requirements:**
- PHP >= 8.4
- Symfony >= 8.0
- Composer >= 2.0



