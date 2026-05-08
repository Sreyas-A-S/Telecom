# PDF Template Standards

This project uses a common PDF standard for all exports.

## 1) Layout
- All PDF templates must extend `layouts.pdf`.
- Shared organization header/footer must come only from `resources/views/layouts/pdf.blade.php`.
- Organization details are sourced from `organization-settings` (`Setting` keys):
  - `organization_name`
  - `organization_address`
  - `organization_phone`
  - `organization_website`
  - `organization_logo`

## 2) Common Design
- Use the shared report heading partial:
  - `@include('pdf.partials.report-header', ['title' => '...', 'subtitle' => '...'])`
- Keep typography/colors aligned with `layouts.pdf` utilities.
- Avoid ad-hoc custom document headers inside individual templates.

## 3) Naming Convention
- Use kebab-case file names for report templates:
  - `pdf.blade.php` for primary module report
  - `pdf-<report-name>.blade.php` for additional reports
- Examples:
  - `clients/pdf.blade.php`
  - `clients/pdf-list.blade.php`
  - `leads/pdf-task-overview.blade.php`

## 4) File Structure
- Keep PDF templates within their feature folder:
  - `resources/views/<module>/pdf*.blade.php`
- Keep reusable shared pieces under:
  - `resources/views/pdf/partials/`

## 5) Controller Convention
- Controllers should reference templates with matching kebab-case view names.
- Example:
  - `Pdf::loadView('clients.pdf-list', $data)`
