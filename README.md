<div align="center">
    <img src="docs/screenshots/inframatrix_banner.png" alt="InfraMatrix Banner" width="100%">
    <h1>InfraMatrix</h1>
    <p><b>Enterprise-Grade Infrastructure & Project Tracker</b></p>
    <p>🚀 <b>Version 1.0</b> - Production Ready</p>
</div>

---

**InfraMatrix** is a production-ready internal web application designed to track, manage, and consolidate organization-wide infrastructure, external integrations, and project assignments into a single, highly performant dashboard. 

## 🚀 Key Features

- **Project & Environment Management**: Maintain strict uniqueness constraints (1 Staging, 1 UAT, 1 Live) per project while flexibly documenting Git configurations, server locations, and CI/CD status.
- **Advanced Filtering Engine**: Visually distinct table-based filtering allowing exact cross-sections of Projects using specific Servers or Integration Types, complete with precise visual pill badge indicators highlighting exact match parameters.
- **Universal Integrations Tracker**: Store metadata for third-party integrations (e.g., SendGrid, AWS, GitHub) centrally, then selectively bind them to specific project environments.
- **Unified Access Control List (ACL)**: Visually aggregate Spatie Role hierarchies (Global Admins vs Infra Admins) alongside scoped project pivot users (Owners, Editors, Viewers) in a read-only, unified Access Summary tab.
- **Robust Deletion Safety**: Core architectural assets (Servers, Integration Accounts, Integration Types) are mathematically shielded from accidental hard deletion if they are bound to active projects.
- **Secure Architecture**: Secrets and keys are never stored; Identifier fields store safe correlation labels logically decoupling the tracker from sensitive credential sprawl.

## 🛠️ Technology Stack

- **Framework**: Laravel 11.x
- **Admin Panel**: Filament v3.x
- **Database**: MySQL 8.0+
- **CSS Framework**: Tailwind CSS
- **Permissions Framework**: Spatie Laravel Permissions

## 📸 Screenshots

*Check `/docs/screenshots/` for full resolution UI captures.*

| Projects Dashboard | Access Visibility Matrix |
| :---: | :---: |
| ![Projects](/docs/screenshots/projects_dashboard.png) | ![ACL View](/docs/screenshots/access_summary.png) |

| Environment Overview | Integrations Manager |
| :---: | :---: |
| ![Environments](/docs/screenshots/environments_tab.png) | ![Integrations](/docs/screenshots/integrations.png) |

## ⚙️ Installation

InfraMatrix is optimized for seamless deployment onto control panels such as **CloudPanel**, **Plesk**, or **Hostinger**.

```bash
# 1. Clone the repository
git clone https://github.com/your-username/inframatrix.git
cd inframatrix

# 2. Install Dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 3. Environment Configuration
cp .env.example .env
php artisan key:generate

# 4. Finalize Deployment (Fresh Database)
php artisan migrate --force --seed
php artisan optimize
php artisan storage:link
```
> For extensive deployment details and mail configuration steps, see [deploy.md](./deploy.md).

## 🛡️ Security
This is an internal tracking tool designed to log metadata, NOT literal connection strings. Remember to utilize the `value` fields solely for safe Identifiers (e.g. `SENDGRID_PROD_1`), never API keys. All local `.env` setups are ignored to prevent credential leakage.

---

### 👨‍💻 Publisher

**Developed by Muhammed Noufal**  
*Cloud & Infrastructure Engineer*  
🔗 [muhammednoufal.cybermavericx.com](https://muhammednoufal.cybermavericx.com)
