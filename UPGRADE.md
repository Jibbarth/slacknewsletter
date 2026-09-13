# Upgrade Guide

## From Standalone Application to Bundle Structure

The original Slack Newsletter was a standalone Symfony application. The bundle version integrates the functionality as a reusable Symfony bundle.

### Steps to Upgrade
1. **Require the bundle**
   ```bash
   composer require barth/slacknewsletter-bundle
   ```
2. **Register the bundle** (if not using Flex)
   ```php
   // config/bundles.php
   return [
       // ...
       Barth\SlackNewsletterBundle\SlackNewsletterBundle::class => ['all' => true],
   ];
   ```
3. **Move configuration files**
   - Channels are now configured in `config/packages/slack_newsletter.yaml` under the `channels` key.
   - To keep using `channels.json`, use the environment preprocessor: `channels: '%env(json:file:resolve:CHANNELS_FILE)%'`
   - Move `config/packages/parameters.yaml` to `config/packages/slacknewsletter/parameters.yaml`.
4. **Update CLI commands**
   - Commands remain `app:newsletter:*` in the bundle. No alias changes required.
5. **Review services**
   - Any custom services that previously extended the app should now extend the bundle classes.
6. **Run migrations (if any)** – currently none.

### Verify
Run the usual commands to ensure the bundle loads:
```bash
php bin/console cache:clear
php bin/console slacknewsletter:browse
```
If they execute without errors, the upgrade is complete.
