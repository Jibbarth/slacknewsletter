# Implementation Tasks: Shareable Bundle

**Branch**: `001-shareable-bundle` | **Date**: 2026-09-13

## Overview

Transform the existing standalone Symfony application into a shareable Symfony Bundle while maintaining standalone execution capability. This document breaks down the implementation into executable tasks organized by user story priority.

## Task Organization

Tasks are grouped by user story to enable independent implementation and testing:
- **Phase 1**: Setup (project initialization)
- **Phase 2**: Foundational (blocking prerequisites)
- **Phase 3**: User Story 1 - Publish bundle (P1)
- **Phase 4**: User Story 2 - Run bundle standalone (P2)
- **Phase 5**: User Story 3 - Update bundle without breaking consumers (P3)
- **Phase 6**: Polish & Documentation

**Total Estimated Tasks**: 25

---

## Phase 1: Setup

**Goal**: Initialize bundle structure and prepare for transformation.

**Tasks**:

- [x] T001 Create `config/bundle/` directory structure for isolated bundle configuration
- [x] T002 Create `Resources/public/` directory at repository root for asset publishing
- [x] T003 Create `src/DependencyInjection/` directory for Extension class

---

## Phase 2: Foundational Tasks

**Goal**: Complete blocking prerequisites that all user stories depend on.

**Dependencies**: Phase 1 complete

**Tasks**:

- [x] T004 Update `composer.json` type from `project` to `symfony-bundle`
- [x] T005 Update `composer.json` PSR-4 mapping from `App\\` to `Barth\\SlackNewsletterBundle\\`
- [x] T006 Update `composer.json` license from `proprietary` to `MIT` (or appropriate open-source license)
- [x] T007 [P] Update namespace in `src/Kernel.php` from `App` to `Barth\SlackNewsletterBundle`
- [x] T008 [P] Update Kernel reference in `bin/console` from `App\Kernel` to `Barth\SlackNewsletterBundle\Kernel`
- [x] T009 [P] Update Kernel reference in `public/index.php` from `App\Kernel` to `Barth\SlackNewsletterBundle\Kernel`
- [x] T010 Run `composer dump-autoload` to regenerate autoloader with new PSR-4 mapping

---

## Phase 3: User Story 1 - Publish Bundle (P1)

**Story Goal**: Enable the bundle to be installed via Composer in consumer projects.

**Independent Test**: A fresh Symfony skeleton can require the bundle and access its functionality.

**Dependencies**: Phase 2 complete

### Implementation Tasks

- [x] T011 [US1] Create `src/SlackNewsletterBundle.php` extending `Symfony\Component\HttpKernel\Bundle\Bundle` with `getPath()` method returning `\dirname(__DIR__)`
- [x] T012 [US1] Create `src/DependencyInjection/SlackNewsletterExtension.php` extending `Symfony\Component\DependencyInjection\Extension\Extension`
- [x] T013 [US1] Implement `load()` method in `SlackNewsletterExtension` using `PhpFileLoader` to load `config/bundle/services.php` from `dirname(__DIR__, 2) . '/config/bundle'`
- [x] T014 [US1] Create `config/bundle/services.php` with service auto-configuration: autowire=true, autoconfigure=true, load `Barth\SlackNewsletterBundle\` from `../../src/`, exclude `../../src/DependencyInjection/`, `../../src/Entity/`, `../../src/SlackNewsletterBundle.php`
- [x] T015 [US1] Create `config/bundle/routing.php` using `RoutingConfigurator` to import `@SlackNewsletterBundle/src/Controller/` with annotation type
- [x] T016 [US1] Register `Barth\SlackNewsletterBundle\SlackNewsletterBundle::class => ['all' => true]` in `config/bundles.php`
- [x] T017 [US1] Remove `App\` service declarations from `config/services.yaml` (keep file, remove only the App namespace block)
- [x] T018 [US1] Update all namespace declarations in PHP files under `src/` from `App\` to `Barth\SlackNewsletterBundle\` (excluding Kernel which was already updated)
- [x] T019 [US1] Update all `use` statements in PHP files under `src/` from `App\` to `Barth\SlackNewsletterBundle\`

### Validation Tasks

- [x] T020 [US1] Run `composer install` and verify no autoload errors
- [x] T021 [US1] Run `bin/console debug:container SlackNewsletterBundle` and verify bundle is registered
- [x] T022 [US1] Run `bin/console list app:newsletter` and verify three commands are available

---

## Phase 4: User Story 2 - Run Bundle Standalone (P2)

**Story Goal**: The original repository remains executable as a standalone Symfony app.

**Independent Test**: Running `symfony serve -d` from the repo starts without errors.

**Dependencies**: Phase 3 complete

### Implementation Tasks

- [x] T023 [US2] Create `config/routes/slack_newsletter.yaml` importing `@SlackNewsletterBundle/config/bundle/routing.php`
- [x] T024 [US2] Verify `config/packages/` files still load correctly for standalone execution
- [x] T025 [US2] Verify `.env` file contains required environment variables (SLACK_TOKEN, MAILER_DSN, CHANNELS_FILE)

### Validation Tasks

- [x] T026 [US2] Run `symfony serve -d` and verify server starts without errors
- [x] T027 [US2] Run `bin/console cache:clear` and verify no namespace errors
- [x] T028 [US2] Access `http://localhost:8000/` and verify route resolves (TestMailController or 404 if no root route)
- [x] T029 [US2] Check `var/log/dev.log` for critical errors related to bundle loading

---

## Phase 5: User Story 3 - Update Bundle Without Breaking Consumers (P3)

**Story Goal**: Establish versioning and backward compatibility practices.

**Independent Test**: Consumer projects can update to new minor versions without breaking.

**Dependencies**: Phase 4 complete

### Implementation Tasks

- [x] T030 [US3] Document breaking changes policy in `README.md`: Major (breaking allowed), Minor (backward-compatible), Patch (bug fixes only)
- [x] T031 [US3] Add minimum requirements section to `README.md`: PHP >= 8.4, Symfony >= 8.0, Composer >= 2.0
- [x] T032 [US3] Create `UPGRADE.md` file documenting how to upgrade from standalone app to bundle structure (for existing users)

---

## Phase 6: Polish & Documentation

**Goal**: Complete documentation and ensure production readiness.

**Dependencies**: Phases 3, 4, 5 complete

### Documentation Tasks

- [ ] T033 Update `README.md` with Composer installation instructions for both path repository (local dev) and Packagist (production)
- [ ] T034 Document bundle registration in `README.md` (automatic via Flex, manual fallback)
- [ ] T035 Document routing integration in `README.md` (creating `config/routes/slack_newsletter.yaml`)
- [ ] T036 Document required environment variables in `README.md` (SLACK_TOKEN, MAILER_DSN, CHANNELS_FILE)
- [ ] T037 Document service autowiring examples in `README.md` (NewsletterBuilder, BrowseService)
- [ ] T038 Document available commands in `README.md` (browse, build, send)
- [ ] T039 Document extension points in `README.md` (template overrides, service overrides, configuration overrides)
- [ ] T040 Add troubleshooting section to `README.md` (class not found, bundle not registered, routes not found)

### Final Validation

- [ ] T041 Create test consumer project in `~/Projects/test-consumer` following `README.md` instructions
- [ ] T042 Verify consumer project can install bundle via path repository: `composer require barth/slacknewsletter:@dev`
- [ ] T043 Verify bundle auto-registers in `config/bundles.php` of consumer project
- [ ] T044 Verify commands are available in consumer project: `bin/console list app:newsletter`
- [ ] T045 Verify service autowiring works in consumer project (create test controller using NewsletterBuilder)
- [ ] T046 Run `bin/console debug:autowiring NewsletterBuilder` in both standalone and consumer projects to verify autowiring

---

## Dependency Graph

```
Phase 1 (Setup)
  ↓
Phase 2 (Foundational) ← blocking for all user stories
  ↓
  ├─→ Phase 3 (US1: Publish bundle) ← Independent
  │     ↓
  ├─→ Phase 4 (US2: Run standalone) ← Depends on Phase 3
  │     ↓
  └─→ Phase 5 (US3: Update safety) ← Depends on Phase 4
        ↓
Phase 6 (Polish & Documentation) ← Depends on Phases 3, 4, 5
```

**Story Independence**:
- US1 (Phase 3) can be fully implemented and tested independently after Phase 2
- US2 (Phase 4) depends on US1 being complete (bundle structure must exist)
- US3 (Phase 5) depends on US2 being complete (standalone execution must work)

---

## Parallel Execution Opportunities

### Within Phase 2 (Foundational)
- T007, T008, T009 can run in parallel (different files)

### Within Phase 3 (US1)
- T011, T012 can run in parallel (different files)
- T014, T015 can run in parallel (different files)
- T018, T019 can be done together (same operation: namespace update)

### Within Phase 4 (US2)
- T023, T024, T025 can run in parallel (different concerns)

### Within Phase 6 (Documentation)
- T033-T040 can all run in parallel (different sections of README.md, can be merged)

---

## Implementation Strategy

**MVP Scope** (Minimum Viable Product):
- Phase 1, 2, 3 only
- Delivers: Bundle can be required by consumer projects (US1)
- Validation: Consumer project can install and use the bundle

**Incremental Delivery**:
1. **Release 1**: Phase 1 + 2 + 3 → Bundle installable
2. **Release 2**: + Phase 4 → Standalone execution works
3. **Release 3**: + Phase 5 → Versioning policy established
4. **Release 4**: + Phase 6 → Full documentation

**Recommended Execution Order**:
1. Complete Phase 1 (Setup) - quick, no dependencies
2. Complete Phase 2 (Foundational) - blocking, must be solid
3. Complete Phase 3 (US1) - core feature, highest priority
4. Validate US1 with test consumer project
5. Complete Phase 4 (US2) - ensures backward compatibility
6. Complete Phase 5 (US3) - documents best practices
7. Complete Phase 6 (Polish) - production readiness

---

## Testing Strategy

No automated tests are generated (not requested in spec). Manual validation tasks included in each phase verify functionality through:
- Console commands (`bin/console debug:container`, `bin/console list`)
- Server startup (`symfony serve -d`)
- Log inspection (`var/log/dev.log`)
- Consumer project installation and usage

---

## Notes

- All namespace updates (T018, T019) should be done carefully with find-replace to avoid breaking code
- After T010 (`composer dump-autoload`), verify autoloader works before proceeding to Phase 3
- The bundle must work in dual mode: standalone (with Kernel) and as installed bundle (loaded by consumer's Kernel)
- Asset publishing via `Resources/public/` happens automatically when consumer runs `composer install`
- Template overrides in consumers use `templates/bundles/SlackNewsletterBundle/` directory
