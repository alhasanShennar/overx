# Earning Period Chart API — Single Period

## Endpoint

```
GET /api/client/earning-periods/{id}/chart
```

| Property      | Value                         |
|---------------|-------------------------------|
| Method        | `GET`                         |
| Auth          | Bearer Token (Sanctum)        |
| URL Param     | `id` — ID تبع الـ EarningPeriod |

---

## Response Structure

```json
{
  "status": "success",
  "message": null,
  "data": {
    "period": { ... },
    "chart": { ... },
    "daily_earnings": [ ... ],
    "transactions": [ ... ],
    "stored_earning": { ... } | null
  }
}
```

---

## `period` — معلومات الفترة الكاملة

| Field               | Type      | Description                                    |
|---------------------|-----------|------------------------------------------------|
| `id`                | int       | ID الفترة                                      |
| `start_date`        | string    | تاريخ البداية `YYYY-MM-DD`                     |
| `end_date`          | string    | تاريخ النهاية `YYYY-MM-DD`                     |
| `days_count`        | int       | عدد الأيام                                     |
| `status`            | string    | `pending` / `completed` / `request_pending` / `stored` / `cashed_out` / `rejected` |
| `client_decision`   | string\|null | `cashout` / `store` / `null`              |
| `is_locked`         | bool      | هل الفترة مقفلة                               |
| `is_eligible`       | bool      | هل تقبل طلب جديد                              |
| `requested_at`      | string\|null | تاريخ وقت الطلب                            |
| `processed_at`      | string\|null | تاريخ وقت المعالجة                          |
| `notes`             | string\|null | ملاحظات                                    |
| `total_btc_earned`  | float     | إجمالي BTC المكتسب                             |
| `average_btc_price` | float     | متوسط سعر BTC خلال الفترة                     |
| `total_revenue`     | float     | إجمالي الإيراد بالعملة الورقية                |

---

## `chart` — بيانات الرسم البياني

| Field                | Type    | Description                                      |
|----------------------|---------|--------------------------------------------------|
| `labels`             | array   | تواريخ الأيام `["2026-01-01", "2026-01-02", ...]` |
| `daily_btc`          | array   | كمية BTC لكل يوم                                 |
| `daily_revenue`      | array   | إيراد كل يوم بالعملة الورقية                    |
| `daily_btc_price`    | array   | سعر BTC لكل يوم                                  |
| `cumulative_btc`     | array   | BTC التراكمي حتى كل يوم                         |
| `cumulative_revenue` | array   | الإيراد التراكمي حتى كل يوم                     |

---

## `daily_earnings` — تفصيل يومي

```json
[
  {
    "date": "2026-01-01",
    "btc_earned": 0.0014,
    "btc_price": 88000.00,
    "revenue": 123.20,
    "additional_notes": null
  }
]
```

---

## `transactions` — جميع المعاملات على الفترة

```json
[
  {
    "id": 5,
    "type": "cashout",
    "btc_amount": 0.042,
    "fiat_amount": 3719.10,
    "status": "completed",
    "requested_by": "client",
    "requested_at": "2026-02-01 10:00:00",
    "processed_at": "2026-02-03 14:00:00",
    "notes": null,
    "cashout": {
      "id": 3,
      "amount": 3719.10,
      "btc_amount": 0.042,
      "date": "2026-02-03",
      "status": "completed",
      "receipt": "receipts/abc.pdf",
      "notes": null,
      "cashout_details": {
        "id": 1,
        "method": "bank_transfer",
        "info": "..."
      }
    },
    "stored": null
  }
]
```

| Field          | Type         | Description                                    |
|----------------|--------------|------------------------------------------------|
| `type`         | string       | `cashout` / `store` / `adjustment`             |
| `status`       | string       | `pending` / `completed` / `cancelled` / `rejected` |
| `requested_by` | string       | من أرسل الطلب (`client` / `admin`)             |
| `cashout`      | object\|null | تفاصيل الكاشاوت إذا كانت المعاملة cashout      |
| `stored`       | object\|null | تفاصيل التخزين إذا كانت المعاملة store        |

---

## `stored_earning` — تفاصيل التخزين (إذا الفترة stored)

```json
{
  "id": 2,
  "btc_amount": 0.042,
  "revenue_amount": 3786.72,
  "stored_at": "2026-02-05 09:30:00",
  "notes": null
}
```

---

## أمثلة الأخطاء

| Code | Message                  | السبب                            |
|------|--------------------------|----------------------------------|
| 404  | `Not found.`             | الـ ID غير موجود أو لا يخص العميل |
| 401  | `Unauthenticated.`       | لا يوجد token                    |

---

## Postman

اسم الـ request: **Earning Period Chart (Single)**  
URL: `{{base_url}}/api/client/earning-periods/{{earning_period_id}}/chart`  
Method: `GET`  
Headers: `Authorization: Bearer {{token}}`
