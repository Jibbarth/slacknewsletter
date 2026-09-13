# Research: Shareable Bundle

**Feature**: Shareable Bundle  
**Date**: 2026-09-13

## Decision 1: Vendor Namespace

**Decision**: Use `Barth\SlackNewsletterBundle` as the vendor-specific namespace.

**Rationale**: 
- Matches the existing composer package name `barth/slacknewsletter`
- Follows Symfony naming conventions (VendorName\BundleName)
- Aligns with the author's GitHub handle (Jibbarth → Barth)

**Alternatives considered**:
- `Acme\SlackNewsletterBundle`: Generic vendor, not personalized
- `Jibbarth\SlackNewsletterBundle`: Too specific to GitHub username

## Decision 2: Bundle Class Name

**Decision**: `SlackNewsletterBundle.php` in `src/`

**Rationale**:
- Standard Symfony bundle naming convention
- Matches package name without redundant "Barth" prefix in the class name
- Bundle class lives at PSR-4 root alongside Kernel

**Alternatives considered**:
- `BarthSlackNewsletterBundle`: Redundant vendor prefix

## Decision 3: Configuration Strategy

**Decision**: Isolate bundle configuration in `config/bundle/` with separate `services.php` and `routing.php`.

**Rationale**:
- Clear separation between standalone app config (`config/services.yaml`) and bundle config (`config/bundle/services.php`)
- Bundle Extension loads only `config/bundle/*`, consumer apps remain isolated from standalone boilerplate
- Follows the article's pattern from https://xn--jibbarth-d1a.fr/post/build-shareable-symfony-app/

**Alternatives considered**:
- Merge bundle config into `config/services.yaml`: Would force consumers to load standalone app configuration
- Use YAML for bundle config: PHP config is more flexible and prevents quoting issues with namespace strings

## Decision 4: Asset Publishing

**Decision**: Create `Resources/public/` at repository root for asset publishing.

**Rationale**:
- Symfony Flex automatically symlinks assets from `Resources/public/` when the bundle is installed
- Prevents recursive asset installation issues
- Standard Symfony bundle convention since Symfony 4

**Alternatives considered**:
- Use `public/` directly: Would conflict with standalone app structure and cause recursive installation

## Decision 5: Composer Package Type

**Decision**: Change composer.json `type` from `project` to `symfony-bundle`.

**Rationale**:
- Signals to Composer and Symfony Flex that this is a reusable bundle
- Enables automatic bundle registration in consumer projects
- Required for Symfony Flex recipes to work

**Alternatives considered**:
- Keep `type: project`: Would not trigger bundle auto-registration

## Decision 6: Extension Class

**Decision**: Create `src/DependencyInjection/SlackNewsletterExtension.php` to load bundle configuration.

**Rationale**:
- Required by Symfony's DependencyInjection component to load bundle services
- Naming convention: `{BundleName}Extension` (without "Bundle" suffix)
- Loads `config/bundle/services.php` via PhpFileLoader

**Alternatives considered**:
- Rely on Kernel to load config: Would not work when bundle is used by other projects

## Decision 7: Routing Registration

**Decision**: Create `config/routes/slack_newsletter.yaml` in the standalone app that imports `@SlackNewsletterBundle/config/bundle/routing.php`.

**Rationale**:
- Consumer apps can import this route file or provide their own prefix
- Follows standard Symfony bundle routing pattern (`@BundleName` notation)
- Allows consumers to customize route prefixes

**Alternatives considered**:
- Auto-import routing in Extension: Less flexible for consumers who want custom prefixes

## Decision 8: Kernel and Entry Point Updates

**Decision**: Update `src/Kernel.php`, `bin/console`, and `public/index.php` to use the new namespace.

**Rationale**:
- Kernel must use the new PSR-4 namespace for autoloading to work
- Entry points must reference the new Kernel location
- Maintains standalone app execution after transformation

**Alternatives considered**:
- Keep Kernel in `App\` namespace: Would break PSR-4 consistency and confuse consumers

## Decision 9: Services Exclusions

**Decision**: Exclude `src/DependencyInjection/`, `src/Entity/`, `src/SlackNewsletterBundle.php`, and `src/Kernel.php` from auto-wiring.

**Rationale**:
- DependencyInjection classes are infrastructure, not services
- Entity classes should be excluded per Symfony best practices
- Bundle and Kernel classes are not services
- Prevents auto-registration conflicts

**Alternatives considered**:
- Auto-wire everything: Would cause registration errors for non-service classes

## Best Practices Applied

- Follow Symfony 8 bundle structure conventions
- Use PHP config files for better IDE support and type safety
- Maintain backward compatibility with standalone execution
- Separate bundle concerns from standalone app concerns
- Use standard Symfony Flex asset publishing mechanism
