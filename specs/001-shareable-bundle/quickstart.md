# Quickstart Validation Guide: Shareable Bundle

**Feature**: Shareable Bundle  
**Date**: 2026-09-13

## Purpose

This guide provides runnable validation scenarios that prove the bundle transformation works end-to-end, both as a standalone application and as an installed bundle in a consumer project.

## Prerequisites

- PHP 8.4+ with required extensions (ext-iconv, ext-json)
- Composer 2.0+
- Symfony CLI (optional, for `symfony serve`)

## Scenario 1: Standalone Execution (Post-Transformation)

**Objective**: Verify the transformed application still runs standalone.

### Setup

```bash
cd /home/barth/Projects/SlackNewsletter
composer install
```

### Test Commands

```bash
# Verify console works
bin/console list app:newsletter

# Expected output: Three commands listed:
#   app:newsletter:browse
#   app:newsletter:build
#   app:newsletter:send

# Start dev server
symfony serve -d

# Or using PHP built-in server
php -S localhost:8000 -t public/

# Access root route (should hit the TestMailController)
curl http://localhost:8000/
```

### Expected Outcomes

- **Console**: Commands list without errors
- **Server**: Starts without namespace errors
- **Root route**: Returns response from `TestMailController` (or 404 if no route defined yet)
- **No errors** in `var/log/dev.log` related to namespace or bundle loading

### Validation Checklist

- [ ] `bin/console` executes without "Class not found" errors
- [ ] `symfony serve` starts successfully
- [ ] `var/log/dev.log` contains no critical errors
- [ ] Services autowiring works (test with `bin/console debug:autowiring NewsletterBuilder`)

---

## Scenario 2: Bundle Installation (Consumer Project)

**Objective**: Verify a fresh Symfony project can install and use the bundle.

### Setup

```bash
# Create test consumer project
cd ~/Projects
composer create-project symfony/skeleton test-consumer
cd test-consumer

# Configure path repository
# Edit composer.json and add:
#   "repositories": [
#     {"type": "path", "url": "../SlackNewsletter"}
#   ]

# Require the bundle
composer require barth/slacknewsletter:@dev
```

### Test Commands

```bash
# Verify bundle registration
bin/console debug:container SlackNewsletterBundle

# Expected: Shows the bundle is registered

# List bundle commands
bin/console list app:newsletter

# Expected: Three commands available

# Add routing configuration
cat > config/routes/slack_newsletter.yaml << 'EOF'
slack_newsletter:
    resource: "@SlackNewsletterBundle/config/bundle/routing.php"
EOF

# Verify routes are loaded
bin/console debug:router | grep slack_newsletter

# Start server and test
symfony serve -d
curl http://localhost:8000/
```

### Expected Outcomes

- **Composer install**: Completes without errors, bundle appears in `vendor/barth/slacknewsletter`
- **Bundle registration**: Automatically added to `config/bundles.php`
- **Commands available**: All three newsletter commands listed
- **Routing**: Bundle routes are accessible
- **Assets**: Published to `public/bundles/slacknewsletter/` (if any exist)

### Validation Checklist

- [ ] `composer require` completes successfully
- [ ] `config/bundles.php` contains `SlackNewsletterBundle::class`
- [ ] `bin/console debug:container` shows bundle services
- [ ] Bundle commands execute without namespace errors
- [ ] Routes from bundle are accessible

---

## Scenario 3: Service Autowiring (Consumer Project)

**Objective**: Verify consumer projects can autowire bundle services.

### Setup

Continue from Scenario 2 (consumer project with bundle installed).

### Test Code

Create `src/Controller/TestController.php`:

```php
<?php

namespace App\Controller;

use Barth\SlackNewsletterBundle\Builder\NewsletterBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/test-bundle', name: 'test_bundle')]
    public function test(NewsletterBuilder $builder): Response
    {
        return new Response('NewsletterBuilder autowired successfully: ' . get_class($builder));
    }
}
```

### Test Commands

```bash
# Clear cache
bin/console cache:clear

# Test the route
curl http://localhost:8000/test-bundle
```

### Expected Outcomes

- **Response**: `NewsletterBuilder autowired successfully: Barth\SlackNewsletterBundle\Builder\NewsletterBuilder`
- **No errors**: No autowiring exceptions

### Validation Checklist

- [ ] `NewsletterBuilder` autowires without configuration
- [ ] Response shows correct fully-qualified class name
- [ ] Other bundle services also autowire (test `BrowseService`, `NewsletterRender`, etc.)

---

## Scenario 4: Backward Compatibility (Existing Installation)

**Objective**: Verify the transformation doesn't break existing installations.

### Setup

If the bundle was previously installed in another project before transformation:

```bash
cd ~/Projects/existing-consumer
composer update barth/slacknewsletter
```

### Expected Outcomes

- **Composer update**: Completes without errors
- **No breaking changes**: Existing code using `App\` namespace is updated by the maintainer
- **Routes still work**: Previously configured routes remain functional

### Validation Checklist

- [ ] `composer update` completes
- [ ] No deprecated namespace warnings
- [ ] Routes continue to resolve
- [ ] Commands still available

---

## Rollback Plan

If validation fails at any step:

1. **Revert composer.json changes**:
   ```bash
   git checkout composer.json composer.lock
   composer install
   ```

2. **Revert namespace changes**:
   ```bash
   git checkout src/ config/ bin/ public/
   ```

3. **Regenerate autoloader**:
   ```bash
   composer dump-autoload
   ```

---

## Success Criteria Reference

Links to acceptance criteria from [spec.md](spec.md#success-criteria):

- **SC-001**: Consumer project installs bundle in < 5 minutes → Scenario 2
- **SC-002**: Standalone app starts without errors → Scenario 1
- **SC-003**: Updates don't break consumers → Scenario 4
- **SC-004**: Documentation completeness ≥ 90% → See [contracts/integration.md](contracts/integration.md)

---

## Troubleshooting

### Issue: Class not found errors

**Cause**: Autoloader not regenerated after namespace change.

**Fix**:
```bash
composer dump-autoload
```

### Issue: Bundle not registered

**Cause**: Symfony Flex didn't auto-register (Flex disabled or old version).

**Fix**: Manually add to `config/bundles.php`:
```php
Barth\SlackNewsletterBundle\SlackNewsletterBundle::class => ['all' => true],
```

### Issue: Routes not found

**Cause**: Consumer project didn't import bundle routing.

**Fix**: Create `config/routes/slack_newsletter.yaml` per [contracts/integration.md](contracts/integration.md#routing-integration).
