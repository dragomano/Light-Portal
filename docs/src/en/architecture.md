---
description: Detailed overview of Light Portal's architecture and design patterns
---

# Portal Architecture

Light Portal follows a modular, object-oriented architecture designed for extensibility and maintainability. Here's an overview of the main components:

## Core Components

- **PortalApp**: Main application class that initializes and coordinates all portal functionality.

- **ServiceProvider**: Dependency injection container for managing services and dependencies.

- **Integration**: Handles integration with SMF core systems and hooks.

## Data Layer

- **Models**: Business logic classes (e.g., BlockModel, PageModel, CategoryModel) that handle data validation and operations.

- **Repositories**: Data access layer classes (e.g., BlockRepository, PageRepository) that manage database interactions.

- **Factories**: Object creation factories for models and other components.

## User Interface Layer

- **UI Components**: Organized into Fields (form inputs), Partials (reusable UI elements), Tables (data display), and Views (page templates).

- **Renderers**: Template rendering engines (Blade, PurePHP) for generating HTML output.

## Plugin System

- **Plugin Architecture**: Extensible system allowing custom blocks, editors, parsers, and other extensions.

- **Event System**: Hook-based event system for customizing behavior at various points.

## Utilities

- **Utils**: Helper classes for common operations (caching, content processing, file handling, etc.).

- **Enums**: Strongly typed enumerations for content types, permissions, statuses, and other constants.

## Key Design Patterns

- **MVC-like Structure**: Separation of data, logic, and presentation.

- **Observer Pattern**: Hook system for event-driven extensions.

- **Factory Pattern**: Object creation through dedicated factories.

- **Repository Pattern**: Centralized data access.

This architecture ensures that Light Portal is both powerful and easy to extend while maintaining performance and security.
