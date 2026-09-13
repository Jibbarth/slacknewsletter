# Implementation Plan: Shareable Bundle

**Branch**: `001-shareable-bundle` | **Date**: 2026-09-13 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/001-shareable-bundle/spec.md`

## Summary

Transform the existing standalone Symfony application into a shareable Symfony Bundle while maintaining the ability to run as a standalone app. The technical approach involves renaming the root namespace to a vendor-specific one, introducing a Bundle class, and isolating bundle configuration in `config/bundle/` to be loaded via an Extension class.

## Technical Context

**Language/Version**: PHP 8.4

**Primary Dependencies**: Symfony 8.0, Symfony Flex, Composer

**Storage**: N/A (The app handles internal storage via `src/Storage/`, but the bundle transformation itself doesn't change the storage engine)

**Testing**: PHPUnit (standard Symfony test suite)

**Target Platform**: Linux server / PHP runtime

**Project Type**: symfony-bundle (shareable) + standalone-app

**Performance Goals**: No degradation in boot time for standalone or consumer apps.

**Constraints**: Must remain executable standalone without additional configuration.

**Scale/Scope**: Conversion of a single project structure.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

The project constitution is currently a template and contains no active constraints or principles. No violations detected.

## Project Structure

### Documentation (this feature)

```text
specs/001-shareable-bundle/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
src/
├── SlackNewsletterBundle.php   # New Bundle class
├── DependencyInjection/         # New Extension class
│   └── SlackNewsletterExtension.php
├── Builder/
├── Collection/
├── Command/
├── Controller/
├── Model/
├── Parser/
├── Render/
├── Repository/
├── Service/
└── Storage/

config/
├── bundle/                     # New bundle-specific config
│   ├── services.php
│   └── routing.php
├── bundles.php                 # Updated to include the new bundle
└── routes/
    └── slack_newsletter.yaml    # Updated to import bundle routing

public/
└── index.php                   # Updated to use new Kernel namespace

composer.json                   # Updated type and PSR-4
```

**Structure Decision**: Modified Single Project. The app remains a single repository but adopts the Symfony Bundle directory structure for the core logic, while maintaining the `public/` and `config/` directories required for standalone execution.

## Complexity Tracking

No constitution violations.

---

## Phase 0: Research Complete ✓

**Output**: [research.md](research.md)

All technical decisions resolved:
- Vendor namespace: `Barth\SlackNewsletterBundle`
- Bundle class: `SlackNewsletterBundle.php`
- Configuration strategy: Isolated `config/bundle/` directory
- Asset publishing: `Resources/public/`
- Composer type: `symfony-bundle`
- Extension class: `SlackNewsletterExtension`
- Routing registration: Import via `@SlackNewsletterBundle` notation
- Entry points: Update Kernel, console, index.php
- Service exclusions: DependencyInjection, Entity, Bundle, Kernel

---

## Phase 1: Design Complete ✓

**Outputs**: 
- [data-model.md](data-model.md)
- [contracts/integration.md](contracts/integration.md)
- [quickstart.md](quickstart.md)

**Data Model**: Structural transformation entities defined (ComposerPackage, BundleClass, ExtensionClass, BundleConfiguration). No domain entity changes.

**Contracts**: Integration contract defined for consumer projects (Composer installation, bundle registration, routing, service access, commands, assets).

**Quickstart**: Four validation scenarios documented (standalone execution, bundle installation, service autowiring, backward compatibility).

**Constitution Re-check**: No violations. Template constitution contains no active constraints.

---

## Next Steps

Run `/speckit.tasks` to generate implementation tasks from this plan.
