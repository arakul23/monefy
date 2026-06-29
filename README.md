# Monefy — Invoice Management

### Як підняти проект

```
docker compose up --build -d
```

- **Frontend:** http://localhost:3001/invoices
- **API:** http://localhost:8080/api/invoices

Міграції і сідер запускаються автоматично при збірці контейнерів(`RUN_MIGRATIONS=true`, `RUN_SEEDERS=true`).

---
### 1. Як ти структурував frontend і backend?

### Backend

```
app/
├── Enum/
│   └── InvoiceStatus.php          # Backed enum: Pending | Approved | Rejected
├── Http/
│   ├── Controllers/
│   │   └── InvoiceController.php  # index, store, show, update
│   ├── Requests/
│   │   ├── CreateInvoiceRequest.php   # Validation: unique number, gross = net + vat, dates
│   │   └── UpdateInvoiceRequest.php   # Merge з CreateInvoiceRequest щоб не дублювати код, перевірка статусу інвойса
│   └── Resources/
│       └── InvoiceResource.php
└── Models/
    └── Invoice.php
routes/
└── api.php
database/
├── migrations/
└── seeders/InvoiceSeeder.php
```

**API endpoints:**

| Method | Endpoint | Description                                  |
|--------|----------|----------------------------------------------|
| GET | `/api/invoices` | Список інвойсів сортирований по `created_at` |
| GET | `/api/invoices/{id}` | Перегляд інвойса                             |
| POST | `/api/invoices` | Створення інвойса                            |
| PUT | `/api/invoices/{id}` | Оновлення інвойса                            |

### Frontend (`/frontend`)

```
app/
├── components/
│   └── InvoiceForm.vue        # Загальна форма для сторінок створення і апдейту інвойса
├── pages/
│   └── invoices/
│       ├── index.vue          # /invoices          — список інвойсів з пагінацією
│       ├── create.vue         # /invoices/create   — Створення інвойсу
│       └── [id]/
│           ├── index.vue      # /invoices/[id]     — перегляд інвойсу
│           └── edit.vue       # /invoices/[id]/edit — апдейт інвойсу
└── app.vue
nuxt.config.ts
```


---

На фронті структура наступна: сторінки згруповані в директорії `invoices/` за файловим роутингом Nuxt. Один компонент `InvoiceForm.vue` використовується і для створення, і для редагування — сторінка передає `schema`/`state` та обробляє сабміт, а компонент відповідає лише за рендер форми.

На беку використовується стандартний Laravel-підхід: `FormRequest` для валідації, тонкий `Controller`, `Resource` для формування відповіді. Бізнес-правило "редагувати можна тільки `pending` інвойси" знаходиться в `UpdateInvoiceRequest::authorize()`.

### 2. Які компроміси ти зробив і чому?

- **Auto-increment замість UUID**: в даному випадку autoincrement виглядає легше. В проді краще використовувати UUID
- **Пагінація на клієнті**: реалізована через TanStack Table без серверного `LIMIT/OFFSET`. Добре для невеликого обсягу даних, але якщо інвойсів буде наприклад 100 000 то такий підхід не підійде.
- **Немає аутентифікації**: в тз вказано що вона не потрібна.
- **Не виніс наповнення бази у фабрику**: не бачу сенсу на малому об'ємі даних

### 3. Що б ти покращив у production-версії?

- Додав би аутентифікацію
- Серверна пагінація, фільтрація та пошук
- UUID для первинних ключів.
- Як мінімум додати кілька юніт тестів
- Логування помилок (наприклад через Sentry)

### 4. Які UX edge cases ти врахував?

- **Loading skeletons** на сторінках списку та деталей
- **Кнопка Edit прихована** для Approved/Rejected інвойсів — у дропдауні відображається тільки для Pending.
- **Бек повертає помилку** якщо інвойс має статус Approved або Rejected
- **`gross_amount` read-only**, перераховується автоматично при зміні `net_amount` або `vat_amount`.
- **Empty state** коли список інвойсів порожній.
- **"Not found" стан** на сторінці деталей для невідомих ID.
- **Toast-сповіщення** при успіху та помилці створення/оновлення.
