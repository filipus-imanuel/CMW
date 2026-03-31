# Employees Module — Business Logic

**Last Updated**: 2026-04-01

---

## 1. Domain Overview

The Employees module manages application **users** and their **roles & permissions**. It uses Spatie Permission for RBAC (Role-Based Access Control). All authentication is handled by Laravel Fortify.

### Sub-Modules

| Sub-Module | Folder | Purpose |
|------------|--------|---------|
| User | `Employees\User\` | CRUD for application users |
| Role | `Employees\Role\` | CRUD for roles + permission assignment + user assignment |

---

## 2. Key Entities

| Entity | Table | Model | Purpose |
|--------|-------|-------|---------|
| User | `users` | `App\Models\User` | Application user with auth credentials |
| Role | `roles` | Spatie `Role` | Named role with permission set |
| Permission | `permissions` | Spatie `Permission` | Individual permission (e.g., `view company`) |
| Department | `departments` | `App\Models\CMW\Master\Department` | Organizational unit (optional FK on users) |

---

## 3. User Management

### 3.1 Create (`Employees\User\Create`)

- **Permission**: `create user`
- **Fields**: name, email, password (confirmed), department (optional), phone (optional), is_active
- **Validation**: email unique, password min 8 chars
- **Redirect**: Users index

### 3.2 Edit (`Employees\User\Edit`)

- **Permission**: `edit user`
- **Fields**: same as Create, password optional (only updates if provided)
- **Email**: unique constraint excludes current user ID

### 3.3 Index (`Employees\User\Index` + `IndexDataTable`)

- **Permission**: `view user`
- **DataTable**: Rappasoft, searchable by name/email
- **Actions**: Edit

### 3.4 User Fields

| Column | Type | Rules |
|--------|------|-------|
| `name` | string(255) | Required |
| `email` | string(255) | Required, email, unique |
| `password` | string | Required on create, optional on edit, min 8, confirmed |
| `department_id` | FK → departments | Optional |
| `phone` | string(20) | Optional |
| `is_active` | boolean | Default true |

---

## 4. Role Management

### 4.1 Create (`Employees\Role\Create`)

- **Permission**: `create role`
- **Fields**: role name (unique), copy_from_role_id (optional)
- **Copy permissions**: If source role selected, copies all its permissions to the new role
- **Protected**: Cannot see/copy "Super Admin" in dropdown
- **Redirect**: Roles index

### 4.2 Edit (`Employees\Role\Edit`)

- **Permission**: `edit role`
- **Features**: Edit role name + manage permission assignment
- **Permission matrix**: Displays all permissions grouped by module (Master Data, Partners, Inventory, System, Sales, Warehouse, Employee, Extra Permissions)
- **Search**: Filterable search across permission names
- **Toggle**: Individual permission toggle + select all per resource
- **Protected roles**: "Super Admin" name cannot be changed

### 4.3 User Assignment (`Employees\Role\User`)

- **Permission**: `edit role`
- **Purpose**: Assign/unassign users to a role
- **Scope**: Shows all active users (excludes user ID 1, i.e., first admin)
- **Search**: Filter by name, email, or department
- **Actions**: Toggle individual, select all, deselect all

### 4.4 Index (`Employees\Role\Index` + `IndexDataTable`)

- **Permission**: `view role`
- **Actions**: Edit (permissions), Manage Users

---

## 5. Default Roles

Defined in `PermissionHelper::getRolePermissions()`:

| Role | Scope |
|------|-------|
| Super Admin | All permissions |
| Management | View-only on everything |
| Admin | Full CRUD on masters, partners, inventory, employees + system settings |
| Finance | Financial master data + item price approval + AR invoice/payment + payment methods |
| Sales | Customers + partner addresses + sales transactions + view items/financials |
| Purchasing | Suppliers + partner addresses + view items/financials |
| Warehouse | Warehouse + items + stock adjustment + delivery order + warehouse return |

---

## 6. Permission Matrix

| Permission | Actions |
|------------|---------|
| `user` | `view`, `create`, `edit`, `delete` |
| `role` | `view`, `create`, `edit`, `delete` |

---

## 7. Routes

| Route Name | URL | Component |
|------------|-----|-----------|
| `employees.users.index` | `/cmw/employees/users` | `Employees\User\Index` |
| `employees.users.create` | `/cmw/employees/users/create` | `Employees\User\Create` |
| `employees.users.edit` | `/cmw/employees/users/{id}/edit` | `Employees\User\Edit` |
| `employees.roles.index` | `/cmw/employees/roles` | `Employees\Role\Index` |
| `employees.roles.create` | `/cmw/employees/roles/create` | `Employees\Role\Create` |
| `employees.roles.edit` | `/cmw/employees/roles/{id}/edit` | `Employees\Role\Edit` |
| `employees.roles.user` | `/cmw/employees/roles/{id}/users` | `Employees\Role\User` |

---

## 8. Related Files

| Area | Path |
|------|------|
| User Model | `app/Models/User.php` |
| Components | `app/Livewire/Employees/User/`, `app/Livewire/Employees/Role/` |
| Permission Helper | `app/Helpers/CMW/PermissionHelper.php` |
| Fortify Provider | `app/Providers/FortifyServiceProvider.php` |
