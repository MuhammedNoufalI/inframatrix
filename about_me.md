# InfraMatrix - Application Guide (v1.0)
*(This file should be updated whenever new features or requirements are added)*

## Application Overview
Welcome to **InfraMatrix (Version 1.0)**, the production-ready internal application for tracking and managing Servers, Git Providers, Integration Types, and Projects.

Here is a complete breakdown of every menu, button, and functionality available in the admin panel.

---

## 🧭 Dashboard
- **Overview Stats**: Quick metrics regarding the system.
- **Integration Accounts Usage Widget**: 
  - Displays a data table of all Services (e.g., SendGrid, reCAPTCHA).
  - Shows the specific `Account Name` (e.g., `uae1-sendgrid@tw.com`).
  - Displays the live **Linked Projects** count. **Important**: This explicitly counts *unique projects* across all environments. If a project uses an account in both staging and live environments, it still strictly counts as `1` linked project.
  - Automatically updates its data every 60 seconds (optimized for database health).

---

## 🏢 Project Management

### Projects
The core of the application where you manage your application deployments.

**List View Features:**
- **Search Bar**: Search by Project Name, Environment URLs, or Status.
- **Environment URLs Column**: Displays a bulleted list of all live and staging URLs attached to a project.
- **Advanced Filters (Excel-style)**:
  The Projects list includes robust multi-select, searchable dropdowns representing an exact "who uses what" matrix across all nested environments.
  - **Status**: Active, On Hold, Archived.
  - **Environment Type**: staging, uat, live.
  - **Server**: Shows any projects hosted on the selected servers.
  - **Git Provider**: Projects tracked by the selected repositories.
  - **Integration Type**: Any project mapping to specific services (e.g. SendGrid, reCAPTCHA).
  - **Integration Account**: Shows any project where *any* of its mapped environments utilize the precise service account (e.g., `uae1-sendgrid@tw.com`).
  - **Integration Identifier / Config ID**: Custom text search matching the typed configuration strings.
  - **CI/CD Configured**: Filter by projects that have (or lack) continuous deployment setups.
  - *Behavior Rule*: If a single project uses the same Account across multiple environments (e.g. staging and live), the filter will count the result only once. Combining multiple filters inherently uses an `AND` logic overlay.

**Quick Insights (Visual Indicators)**:
When utilizing any of the Advanced nested filters above (e.g. searching for a specific Server or Integration Account), the resulting Project list preserves the exact same Staging/UAT/Live structure, but visually injects context badges.
- **Example**: If filtering by Server `Hostinger`, the system will fetch the matching overall project, but explicitly append a small, elegant `[Matched Server]` badge next to the exact URL(s) running on Hostinger, explicitly answering "why is this project showing up?" without sacrificing the UI layout. If the corresponding Live environment runs on AWS, it safely appears without the badge.

- **New Project (Button)**: Opens the creation form.

**Project Form (Create/Edit):**
Organized into interactive tabs.

**Tab: Overview**
- **Name**: Name of the project.
- **Status Select**: Active, On Hold, Archived.
- **Notes Text area**: General documentation for the project.

**Tab: Environments**
- Allows defining deployment targets. **Rule**: A project is strictly confined to exactly *one* Staging, *one* UAT, and *one* Live environment. You cannot create duplicates.
- **Type Dropdown**: Select `staging`, `uat`, or `live`. The system dynamically prevents selecting a type that is already in use by the current project.
- **URL**: The web address of the environment.
- **Server ID Dropdown**: Links to Master Data -> Servers.
- **Git Provider Dropdown**: Links to Master Data -> Git Providers.
- **Repo URL & Branch**: Source code tracking.
- **CI/CD Configured Toggle**: 
  - If switched **ON**, everything is fine.
  - If switched **OFF**, a required **"Reason"** text box dynamically appears.
- **Checklist Attachment**: An upload field that *only* appears if the Environment Type is set to `live`.

**Section: Environments -> Integrations (Nested)**
- Allows linking external services directly to that specific environment.
- **Type Dropdown**: Selects the Integration (e.g., SendGrid). *Note: Only Integration Types marked as "Active" in the Master Data appear here.* What happens next depends on the Integration's *Behavior*:
  - *If Behavior = `account_select_optional`*: A dropdown appears asking you to select the specific **Account** (e.g., `uae1-sendgrid`), along with an optional generic **Identifier / Config ID** text input.
  - *If Behavior = `generic_value`*: Only the **Identifier / Config ID** text input appears, and it is marked as *required*. 
  - *Security Note:* Never store raw API secrets in the `Identifier` field. Use this field strictly to store safe labels (e.g. `sendgrid-api-key-id-2`). Store actual secrets in a locked `.env` vault.

**Tab: Assignments**
- Determines who is allowed to view or edit the project.
- **Infra User**: Can only see and edit the projects explicitly assigned to them in this tab.
- **Viewer**: A user with no explicit role in the system. Can only *see* the projects assigned to them here. They cannot edit them.

---

## ⚙️ Master Data
This section contains configuration tables that feed into the Project dropdowns. Changes here affect the whole system.

### Servers
- Tracks physical/cloud infrastructure.
- **Fields**: Server Name, Subscription Name, Location, Provider, Panel, OS versions, IP addresses, AMC status, and an Active/Inactive toggle.
- **Dynamic Configuration**: The `Provider` and `Panel` fields are completely dynamic. While standard defaults (AWS, Azure, CloudPanel) are present, whenever a user imports an Excel file with a custom string (e.g., "DigitalOcean" or "cPanel") or uses the "+" button within the dropdown inside the UI, the system instantly learns and adds the new value globally.

### Git Providers
- Tracks version control platforms (e.g., GitHub, GitLab).
- **Fields**: Name, Base URL.

### Integration Types & Accounts
- Designed using a highly efficient **Parent/Child** relationship.
- **List View**: Shows the available Services (SendGrid, reCAPTCHA, Cloudflare, Akamai).
- **Form (Edit Integration Type)**:
  - **Name**: The service name.
  - **Behavior Dropdown**: Determines how the Project form reacts when this service is selected (`Generic Value` vs `Account Select Optional`).
  - **Active Toggle**: Activating this allows it to be used. Switching it off archiving it so it disappears from the Project forms, without breaking history.
- **Deletion Safety**: 
  - You **cannot** delete an Integration Type if it has been attached to any project's environment. The system will throw an error blocking the action. You must "Archive" (deactivate) the service instead to preserve historical integrity.
- **Accounts Relation Manager (Data Table at bottom of the Edit page)**:
  - Inside the "Edit Integration Type" window, there is a child table representing all Accounts owned by TW for that specific service.
  - **Create Action (Button)**: Opens a modal to instantly add a new Account (e.g. `uk-marketing-sendgrid`).

---

## 📥 Data Management (Excel Engine)
Built into the **Settings -> Data Management** dashboard, InfraMatrix ships with a lightning-fast, zero-duplicate Excel Import/Export processing engine using a stable UUID matching system.
- **Download Templates**: Generates pristine `.xlsx` blueprints mapped precisely to the required fields.
- **Process Uploads**: Instead of complex multi-step wizards, simply upload your filled template.
- **Zero-Duplicate Architecture**: The server uses stable `import_uid` trackers to gracefully map rows. When parsing a row:
  - If identical to the database → **Skipped**
  - If a new configuration is parsed → **Created**
  - If an existing record has changed variables → **Updated**

---

## 🛡️ User Management & Roles

### Users
- Managed via `spatie/laravel-permission`.
- Uses role-based access control (RBAC). 
- Detailed Roles:
  - **Admin**: Full, unrestricted access to the entire application.
  - **Infra Admin**: Broad access for infrastructure management.
  - **Infra User**: Limited access. Restricted to editing explicitly assigned projects.
  - **Viewers**: Standard users with no system role. Restricted to purely viewing explicitly assigned projects.

### Invites
- System for securely onboarding new team members via temporary tokens instead of manual password handling.
