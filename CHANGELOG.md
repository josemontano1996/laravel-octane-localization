# Changelog: v0.1.0-alpha
Date: April 19, 2026

Author: Jose Manuel Montano Mengual (jm3develop@gmail.com)

## 🚀 Added
- New Blade Directive: Introduced the @t directive for streamlined translations/localization within Blade templates.
- Dependency Testing: Added new test suites to verify package dependencies.

## 🐞 Fixed
- Locale Detection Stale Data and redirect bugs: Migrated service container bindings from singleton/transient to scoped to prevent cross-request locale detection errors.
- Locale State Consistency: Implemented a reset mechanism for the Locale Manager during the Service Provider boot process to ensure same initial state for FPM and Octane applications.
- Testing Infrastructure: Resolved a merging configuration conflict that was causing issues in the testing environment.
