
# Twill 3 based CMS Stack

- **CMS:** Twill 3 (Area 17)
- **Frontend Stack:** Inertia.js (SSR enabled)
- **UI:** React + Shadcn +  Tailwind CSS


# Twill 3 Architectural Standards & Rules

## Core Philosophy

This project uses **Twill 3**. The primary goal is to maintain a clean separation between CMS configuration (Form Builder), business logic (Repositories), and frontend presentation (Inertia/React).

## 1. Form Builder (Fluent API)

- **Constraint:** Blade-based field definitions are strictly **prohibited**.
- **Requirement:** Use the Fluent API within the `form()` method of `ModuleController` or dedicated `Form` classes.
- **Syntax:** Always chain methods clearly.
  ```php
  $form->add(
      Input::make()->name('title')->label('Title')->translatable()
  );
  ```

## 2. Repository Pattern & Controllers

- **Controller Responsibility:** Keep ModuleController lean. Only handle request entry/exit.
- **Repository Responsibility:** All database interactions, complex filtering, and data transformation must reside in the Repository class.
- **Rule:** Never write raw SQL or complex Eloquent queries inside the controller.

## 3. Media & File Handling

- **Twill Media:** Always use the Twill Media interface.
- **Methodology:** Use $this->medias($item) or related Twill media helpers to retrieve assets.
- **Prohibition:** Do not store image paths as simple strings in database columns; use Twill's internal media-linking system.

## 4. Block & Component Architecture

- **Mapping:** Every Twill Block must have a corresponding React/Inertia component.
- **SSR Safety:** Ensure components are SSR-compatible.
- **Rule:** Avoid using `window` or `document` inside the component body. Use useEffect or useLayoutEffect for client-side side effects.
- **Data Injection:** Pass data to frontend components strictly via props managed by Twill's block parser.

## 5. Validation

- **Requirement:** Every module must utilize a Laravel FormRequest class.
- **Flow:** Controller -> FormRequest (Validation) -> Repository (Persistence).
- **Rule:** Do not validate inputs directly within the Repository or the Controller's method body.

## 6. Implementation Checklist

Before completing any task, verify:

- Is the Form Builder using the Fluent API?
- Is the business logic isolated in a Repository?
- Are media fields handled via Twill's media library?
- Is validation handled by a dedicated FormRequest?