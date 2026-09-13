# Data Model: Shareable Bundle

**Feature**: Shareable Bundle  
**Date**: 2026-09-13

## Overview

This feature involves structural transformation rather than domain data modeling. No new entities, relationships, or database schemas are introduced. The transformation affects the project's organizational structure and how it's packaged for distribution.

## Configuration Entities

### ComposerPackage

**Purpose**: Represents the package metadata for Composer distribution.

**Key Attributes**:
- `name`: `barth/slacknewsletter` (unchanged)
- `type`: Changed from `project` to `symfony-bundle`
- `license`: Changed from `proprietary` to `MIT` (or appropriate open-source license)
- `autoload.psr-4`: Namespace mapping changed from `App\\` → `Barth\\SlackNewsletterBundle\\`

**Validation Rules**:
- Package type must be `symfony-bundle` to enable Flex auto-registration
- PSR-4 namespace must match directory structure

### BundleClass

**Purpose**: Entry point for Symfony's bundle registration system.

**Key Attributes**:
- `namespace`: `Barth\SlackNewsletterBundle`
- `className`: `SlackNewsletterBundle`
- `location`: `src/SlackNewsletterBundle.php`

**Relationships**:
- Extends `Symfony\Component\HttpKernel\Bundle\Bundle`
- Registered in `config/bundles.php`
- Implements `getPath()` to return bundle root directory

### ExtensionClass

**Purpose**: Loads bundle-specific services and configuration into the Symfony DI container.

**Key Attributes**:
- `namespace`: `Barth\SlackNewsletterBundle\DependencyInjection`
- `className`: `SlackNewsletterExtension`
- `location`: `src/DependencyInjection/SlackNewsletterExtension.php`

**Relationships**:
- Extends `Symfony\Component\DependencyInjection\Extension\Extension`
- Loaded automatically by the Bundle class
- Loads `config/bundle/services.php`

### BundleConfiguration

**Purpose**: Isolated configuration for bundle services and routing.

**Key Attributes**:
- `services_file`: `config/bundle/services.php`
- `routing_file`: `config/bundle/routing.php`

**Validation Rules**:
- Services must exclude infrastructure classes (DependencyInjection, Entity, Bundle, Kernel)
- Routing must use annotation/attribute loading from `src/Controller/`

## State Transitions

### Package Type Transition

```
State: Standalone Application (type: project)
  ↓ [Apply bundle transformation]
State: Dual-Mode Package (type: symfony-bundle)
  ↓ [Consumed by another project]
State: Installed Bundle (registered in consumer's config/bundles.php)
```

### Namespace Transition

```
State: App\ namespace
  ↓ [Update composer.json PSR-4 mapping]
  ↓ [Update Kernel.php namespace declaration]
  ↓ [Update entry points (bin/console, public/index.php)]
State: Barth\SlackNewsletterBundle\ namespace
```

## Existing Domain Entities (Unchanged)

The following domain entities from the original application remain unchanged:

- `Channel`: Represents a Slack channel
- `Newsletter\Article`: Represents a link/article in the newsletter
- `Newsletter\Contributor`: Represents a person who shared a link
- `Newsletter\Section`: Represents a section grouping articles

These entities continue to operate as before; the transformation only affects the namespace and packaging structure, not their behavior or relationships.

## Configuration Files Changed

| File | Change Type | Purpose |
|------|-------------|---------|
| `composer.json` | Modified | Update package type and PSR-4 namespace |
| `config/bundles.php` | Modified | Register new `SlackNewsletterBundle` |
| `config/services.yaml` | Modified | Remove `App\` namespace service declarations |
| `config/routes/slack_newsletter.yaml` | Created | Import bundle routing |
| `src/Kernel.php` | Modified | Update namespace to `Barth\SlackNewsletterBundle` |
| `bin/console` | Modified | Update Kernel reference |
| `public/index.php` | Modified | Update Kernel reference |

## No Database Schema Changes

This transformation does not introduce any database migrations, schema changes, or new persistence requirements. All existing storage mechanisms remain unchanged.
