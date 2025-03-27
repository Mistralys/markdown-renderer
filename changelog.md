## v1.0.3 - Site URL enhancements
- SiteURL Processor: Added `setParamsCallback()` to adjust the URL parameters on the fly.
- SiteURL Processor: Now sorting URL parameters alphabetically for consistent URLs.
- Image Processor: Added the `height`, `id` and `alt` attributes to the image command.
- Image Processor: Added docs in the README for the image command.

## v1.0.2 - Added raw HTML support
- Processors: The HTML processor can add raw HTML code.
- Renderer: Added `isValid()` and `getResults()` to check for processor errors.
- Renderer: Fixed a return type doc for PHPStan chaining detection.

## v1.0.1 - Added Site URLs
- Processors: Added the handling of website-internal links.  

## v1.0.0 - Initial release
- Base rendering system.
- Bundled image command.
- Bundled video command.
