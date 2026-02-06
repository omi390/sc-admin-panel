# Service Extended Data – API Spec for Frontend / Node.js

Description of the service-related tables and fields added for the admin panel, so the Node.js backend can expose APIs for the frontend.

---

## Overview

Each **service** can now have:

- **Procedure** – multiple steps (image, title, description)
- **Service note** – multiple notes (title, description, image)
- **Pros and cons** – two lists: pros and cons (each item is a title string)
- **FAQs** – multiple FAQs (title, description)
- **Policy** – multiple policies (title, description)
- **Code** – on the service itself: `code_text` and `code_img` (URL)

All of these are optional. The admin panel saves them when creating/editing a service.

---

## 1. Procedure (steps)

**Table:** `service_procedures`  
**Relation:** Many steps per service (`service_id` → `services.id`)

| Column       | Type         | Nullable | Description                    |
|-------------|--------------|----------|--------------------------------|
| id          | UUID (CHAR 36) | No     | Primary key                    |
| service_id  | UUID         | No       | FK → services.id               |
| image_url   | VARCHAR(500) | Yes      | Step image URL                 |
| title       | VARCHAR(191) | Yes      | Step title                     |
| description | TEXT         | Yes      | Step description               |
| sort_order  | INT UNSIGNED | No       | Order (0, 1, 2, …)             |
| created_at  | TIMESTAMP    | Yes      |                                |
| updated_at  | TIMESTAMP    | Yes      |                                |

**Example payload (one step):**

```json
{
  "id": "uuid",
  "service_id": "uuid",
  "image_url": "https://example.com/step1.png",
  "title": "Step 1 title",
  "description": "Step 1 description",
  "sort_order": 0
}
```

**Suggested API:**  
- GET `/services/:serviceId/procedures` – list steps ordered by `sort_order`.

---

## 2. Service note

**Table:** `service_notes`  
**Relation:** Many notes per service (`service_id` → `services.id`)

| Column       | Type         | Nullable | Description        |
|-------------|--------------|----------|--------------------|
| id          | UUID         | No       | Primary key        |
| service_id  | UUID         | No       | FK → services.id   |
| title       | VARCHAR(191) | Yes      | Note title         |
| description | TEXT         | Yes      | Note body          |
| image       | VARCHAR(500) | Yes      | Note image URL     |
| sort_order  | INT UNSIGNED | No       | Order (0, 1, 2, …) |
| created_at  | TIMESTAMP    | Yes      |                    |
| updated_at  | TIMESTAMP    | Yes      |                    |

**Example payload (one note):**

```json
{
  "id": "uuid",
  "service_id": "uuid",
  "title": "Note title",
  "description": "Note description",
  "image": "https://example.com/note.png",
  "sort_order": 0
}
```

**Suggested API:**  
- GET `/services/:serviceId/notes` – list notes ordered by `sort_order`.

---

## 3. Pros and cons

**Table:** `service_pros_and_cons`  
**Relation:** Many rows per service (`service_id` → `services.id`). Each row is either a “pro” or a “con”.

| Column      | Type         | Nullable | Description                    |
|------------|--------------|----------|--------------------------------|
| id         | UUID         | No       | Primary key                    |
| service_id | UUID         | No       | FK → services.id               |
| title      | VARCHAR(191) | Yes      | One pros/cons item (e.g. “Fast”) |
| prod_or_con| ENUM         | No       | `"pros"` or `"con"`            |
| sort_order | INT UNSIGNED | No       | Order within pros or cons      |
| created_at | TIMESTAMP    | Yes      |                                |
| updated_at | TIMESTAMP    | Yes      |                                |

**Example payload (one item):**

```json
{
  "id": "uuid",
  "service_id": "uuid",
  "title": "Quick delivery",
  "prod_or_con": "pros",
  "sort_order": 0
}
```

**Suggested API:**  
- GET `/services/:serviceId/pros-and-cons` – list all; frontend can split by `prod_or_con` into two arrays (pros and cons).

---

## 4. FAQs

**Table:** `faqs` (existing table; new columns: `title`, `description`)  
**Relation:** Many FAQs per service (`service_id` → `services.id`)

| Column      | Type         | Nullable | Description        |
|------------|--------------|----------|--------------------|
| id         | UUID         | No       | Primary key        |
| service_id | UUID         | Yes      | FK → services.id   |
| question   | TEXT         | Yes      | (legacy) same as title |
| answer     | TEXT         | Yes      | (legacy) same as description |
| **title**  | VARCHAR(191) | Yes      | **Added** – FAQ title/question |
| **description** | TEXT    | Yes      | **Added** – FAQ answer/body   |
| is_active  | BOOLEAN      | No       | Default 1          |
| created_at | TIMESTAMP    | Yes      |                    |
| updated_at | TIMESTAMP    | Yes      |                    |

Use `title` and `description` for the new API; `question`/`answer` can be kept in sync for backward compatibility.

**Example payload (one FAQ):**

```json
{
  "id": "uuid",
  "service_id": "uuid",
  "title": "What is the warranty?",
  "description": "One year warranty is included.",
  "is_active": true
}
```

**Suggested API:**  
- GET `/services/:serviceId/faqs` – list FAQs (prefer returning `title` and `description`).

---

## 5. Policy

**Table:** `service_policies`  
**Relation:** Many policies per service (`service_id` → `services.id`)

| Column       | Type         | Nullable | Description      |
|-------------|--------------|----------|------------------|
| id          | UUID         | No       | Primary key      |
| service_id  | UUID         | No       | FK → services.id |
| title       | VARCHAR(191) | Yes      | Policy title     |
| description | TEXT         | Yes      | Policy body      |
| sort_order  | INT UNSIGNED | No       | Order (0, 1, …)  |
| created_at  | TIMESTAMP    | Yes      |                  |
| updated_at  | TIMESTAMP    | Yes      |                  |

**Example payload (one policy):**

```json
{
  "id": "uuid",
  "service_id": "uuid",
  "title": "Cancellation policy",
  "description": "You can cancel up to 24 hours before.",
  "sort_order": 0
}
```

**Suggested API:**  
- GET `/services/:serviceId/policies` – list policies ordered by `sort_order`.

---

## 6. Code (on the service)

**Table:** `services` (existing; two new columns)

| Column     | Type         | Nullable | Description        |
|-----------|--------------|----------|--------------------|
| **code_text** | TEXT     | Yes      | **Added** – Code or text block |
| **code_img**  | VARCHAR(500) | Yes | **Added** – Code/image URL      |

**Example (in service response):**

```json
{
  "id": "service-uuid",
  "name": "Service name",
  "code_text": "Optional code or text content",
  "code_img": "https://example.com/code-screenshot.png"
}
```

**Suggested API:**  
- GET `/services/:id` (or existing service detail endpoint) – include `code_text` and `code_img` in the response.

---

## Suggested combined “service detail” response

For a single service detail page, the Node.js API can return the service plus all related data in one response, e.g.:

```json
{
  "service": {
    "id": "uuid",
    "name": "...",
    "description": "...",
    "code_text": "...",
    "code_img": "https://..."
  },
  "procedures": [
    { "id": "...", "image_url": "...", "title": "...", "description": "...", "sort_order": 0 }
  ],
  "notes": [
    { "id": "...", "title": "...", "description": "...", "image": "...", "sort_order": 0 }
  ],
  "pros": [
    { "id": "...", "title": "..." }
  ],
  "cons": [
    { "id": "...", "title": "..." }
  ],
  "faqs": [
    { "id": "...", "title": "...", "description": "..." }
  ],
  "policies": [
    { "id": "...", "title": "...", "description": "...", "sort_order": 0 }
  ]
}
```

- **procedures, notes, policies:** ordered by `sort_order`.  
- **pros / cons:** from `service_pros_and_cons` where `prod_or_con === 'pros'` and `prod_or_con === 'con'`, ordered by `sort_order`.

---

## Summary table

| Entity        | Table                  | Relation   | Key fields                                      |
|---------------|------------------------|-----------|-------------------------------------------------|
| Procedure     | service_procedures     | service   | image_url, title, description, sort_order       |
| Note          | service_notes          | service   | title, description, image, sort_order          |
| Pros & cons   | service_pros_and_cons  | service   | title, prod_or_con (pros/con), sort_order       |
| FAQ           | faqs                   | service   | title, description (and question, answer)       |
| Policy        | service_policies       | service   | title, description, sort_order                  |
| Code          | services               | –         | code_text, code_img                             |

All IDs are UUIDs (CHAR(36)). All `service_id` foreign keys reference `services.id` and should use ON DELETE CASCADE (when a service is deleted, its procedures, notes, pros_and_cons, and policies are deleted; FAQs may need to be deleted in app logic if not using cascade).

Use this document in your Node.js project to implement the service-detail and related list endpoints for the frontend.
