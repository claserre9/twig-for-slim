# Improvement Tasks Checklist

## Code Quality & Standards
- [x] Add PHPUnit tests to cover `Twig`, `TwigMiddleware`, and `TwigExtension`.
- [x] Implement PHPStan or Psalm for static analysis.
- [x] Add PHP_CodeSniffer for PSR-12 compliance.
- [x] Improve type hinting and return types across all classes.

## Features & Enhancements
- [x] Add support for Twig Runtime Loaders.
- [x] Implement a way to easily add custom filters and functions to `TwigExtension`.
- [x] Enhance `TwigMiddleware` to support multiple Twig instances if needed.
- [x] Improve `relativePath` logic in `TwigExtension` for more complex base paths.
- [x] Add a `base_path()` Twig function to get the Slim app's base path.

## Documentation
- [x] Expand `README.md` with usage examples and scripts documentation.
- [ ] Add documentation for all available Twig functions (`url_for`, `full_url_for`, etc.).
- [ ] Create a `CONTRIBUTING.md` file.

## Infrastructure
- [x] Set up GitHub Actions for CI (running tests and static analysis).
- [x] Add a `.editorconfig` file.
- [x] Ensure `composer.json` has complete metadata (keywords, homepage, etc.).
- [x] Add composer scripts for common tasks (test, analyze).
- [x] Set up GitHub workflow for Packagist auto-updates.
