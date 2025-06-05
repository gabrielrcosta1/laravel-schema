# Laravel Schema (Development Preview)

> 🧪 This package is currently under development. Features, APIs and behavior may change at any time.

Generate Laravel migration files based on a simple DSL (domain-specific language) written in a single file: `schema.db`.

---

## 📦 Installation

Add the repository to your Laravel project's `composer.json`:

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/gabrielrcosta1/laravel-schema"
  }
]
```

Then run:

```bash
composer require gabrielrcosta1/laravel-schema
```

---

## 📁 What It Does

- Adds two Artisan commands: `schema:create` and `schema:migrate`
- Lets you define your database tables in a single DSL file
- Generates Laravel-compatible migration files into `database/migrations`
- Keeps track of schema changes via internal hash cache
- Lets you run `php artisan migrate` as usual

---

## 🚀 Usage

### 1. Generate the schema file

```bash
php artisan schema:create
```

This will create a file at:

```
/your-laravel-project/database/schema.db
```

With default content for:

- `users`
- `password_reset_tokens`
- `sessions`

---

### 2. Define your schema (example)

You can edit `database/schema.db` like this:

```txt
table posts {
    id primary
    title string
    body text
    published_at timestamp nullable
    user_id foreign:users.id
    timestamps
}
```

---

### 3. Generate migration files

```bash
php artisan schema:migrate
```

This will read `schema.db`, compare hashes, and generate only updated migration files into:

```
database/migrations/
```

---

### 4. Apply the migrations

```bash
php artisan migrate
```

---

## ✅ Commands Summary

| Command               | Description                            |
| --------------------- | -------------------------------------- |
| `schema:create`       | Create `schema.db` with default tables |
| `schema:migrate`      | Generate migration files from schema   |
| `php artisan migrate` | Apply the generated migrations         |

---

## ⚠️ Status

This package is **in development**.  
Use at your own risk in production environments.

Contributions, ideas, and issues are welcome.

---

## 📄 License

MIT — © Gabriel R. Costa
