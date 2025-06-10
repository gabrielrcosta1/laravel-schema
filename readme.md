# Laravel Schema (Development Preview)

> 🧪 This package is currently under development. Features, APIs and behavior may change at any time.

Generate Laravel migration files based on a simple DSL (domain-specific language) written in a single file: `schema.db`.

---

## 📦 Installation
Then run:

```bash
composer require gabrielrcosta1/laravel-schema
```

---

## 📁 What It Does

- Adds Artisan commands:
  - `schema:create`
  - `schema:migrate`
  - `schema:reset`
- Lets you define your database tables in a single DSL file
- Generates Laravel-compatible migration files into `database/migrations`
- Tracks schema changes and generates only incremental migrations
- Supports full or table-specific reset
- Lets you run `php artisan migrate` as usual

---

## 🚀 Usage

### 1. Generate the schema file

```bash
php artisan schema:create
```

This will create the file:

```
/your-laravel-project/database/schema.db
```

With default content for:

- `posts`

---

### 2. Define your schema (example)

Edit `database/schema.db` like this:

```txt
table posts {
    id primary
    title string
    body text
    published_at timestamp nullable
    user_id foreign:users.id
    timestamps
    softDeletes
}
```

---

### 3. Generate migration files

```bash
php artisan schema:migrate
```

This will:

- Parse `schema.db`
- Compare with previous schema
- Generate new migrations:
  - full table migrations
  - incremental `add_` / `remove_` migrations

---

### 4. Apply the migrations

```bash
php artisan migrate
```

---

### 5. Reset generated migrations

If you want to delete generated migrations and schema cache:

#### Reset everything:

```bash
php artisan schema:reset --all
```

#### Reset only one table:

```bash
php artisan schema:reset
# You will be prompted for the table name
```

This deletes:

- The migration files related to the selected table(s)
- The schema cache files (.json and .hash)

---

## ✅ Commands Summary

| Command               | Description                                                 |
| --------------------- | ----------------------------------------------------------- |
| `schema:create`       | Create `schema.db` with default tables                      |
| `schema:migrate`      | Generate migrations from schema (full or incremental)       |
| `schema:reset`        | Delete generated migrations and cache (`--all` or specific) |
| `php artisan migrate` | Apply the generated migrations                              |

---

## ⚠️ Status

This package is **in development**.  
Use at your own risk in production environments.

Contributions, ideas, and issues are welcome.

---

## 📄 License

MIT — © Gabriel R. Costa
