# Trading Module — Client API Guide

This document is for **frontend / mobile developers** integrating the client trading features.

Trading is **fully separate from mining**. It uses its own contracts, monthly periods, earnings, cashouts, and stored balances (USD only — no BTC).

---

## Authentication

All endpoints below require a valid Sanctum token:

```http
Authorization: Bearer {token}
```

Obtain a token via `POST /api/login`.

The authenticated user must have a linked **client** profile (`user.client`).

---

## Base URL

```
/api/client
```

---

## Concepts

| Concept | Description |
|---------|-------------|
| **Trading Contract** | Agreement with amount, file, start/end dates. Separate from mining contracts. |
| **Trading Earning** | Individual profit/loss entry recorded by admin against a contract. |
| **Trading Period** | Calendar month bucket per contract. Earnings in a month roll up into `total_earning`. |
| **Month-end decision** | After the month ends, period status becomes `completed`. Client chooses **cashout** or **store**. |
| **Trading Cashout** | Payout of monthly earnings to client's cashout method (processed by admin). |
| **Trading Stored Earning** | Monthly earnings kept in the client's trading stored balance. |

### Period status flow

```
pending → completed → request_pending → cashed_out | stored
                              ↓
                          rejected → completed (client can decide again)
```

| Status | Meaning |
|--------|---------|
| `pending` | Month still in progress |
| `completed` | Month ended — client can choose cashout or store |
| `request_pending` | Client submitted decision — awaiting admin |
| `cashed_out` | Cashout processed |
| `stored` | Earnings stored in trading balance |
| `rejected` | Admin rejected — client may submit again |

---

## Response format

### Success (single resource)

```json
{
  "status": "success",
  "message": "Optional message",
  "data": { }
}
```

### Success (paginated)

```json
{
  "status": "success",
  "message": null,
  "data": [ ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 42,
    "last_page": 3
  }
}
```

### Error

```json
{
  "status": "error",
  "message": "Human readable error",
  "data": null
}
```

---

## Endpoints

### 1. List trading contracts

```http
GET /api/client/trading-contracts
```

**Query parameters**

| Param | Type | Description |
|-------|------|-------------|
| `status` | string | `active` or `expired` |
| `page` | int | Pagination |

**Example response `data[]` item**

```json
{
  "id": 1,
  "client_id": 3,
  "amount": 50000.00,
  "earning": 4200.50,
  "roi_percent": 8.4,
  "file_url": "https://example.com/storage/trading-contracts/abc.pdf",
  "start_date": "2026-01-01",
  "end_date": "2026-12-31",
  "period_label": "Jan 01, 2026 → Dec 31, 2026",
  "status": "active",
  "notes": null,
  "created_at": "2026-01-15T10:00:00+00:00"
}
```

---

### 2. Show trading contract

```http
GET /api/client/trading-contracts/{id}
```

---

### 3. List trading earnings

```http
GET /api/client/trading-earnings
```

**Query parameters**

| Param | Type | Description |
|-------|------|-------------|
| `trading_contract_id` | int | Filter by contract |
| `trading_period_id` | int | Filter by monthly period |
| `year` | int | Filter by year (use with `month`) |
| `month` | int | Filter by month (1–12) |
| `page` | int | Pagination |

**Example `data[]` item**

```json
{
  "id": 12,
  "client_id": 3,
  "trading_contract_id": 1,
  "trading_period_id": 5,
  "date": "2026-03-15",
  "amount": 850.00,
  "notes": "March week 2 profit",
  "created_at": "2026-03-15T14:00:00+00:00"
}
```

---

### 4. List trading periods (monthly)

```http
GET /api/client/trading-periods
```

**Query parameters**

| Param | Type | Description |
|-------|------|-------------|
| `trading_contract_id` | int | Filter by contract |
| `status` | string | e.g. `completed`, `request_pending` |
| `page` | int | Pagination |

**Example `data[]` item**

```json
{
  "id": 5,
  "client_id": 3,
  "trading_contract_id": 1,
  "period": "March 2026",
  "year": 2026,
  "month": 3,
  "start_date": "2026-03-01",
  "end_date": "2026-03-31",
  "total_earning": 1250.75,
  "status": "completed",
  "client_decision": null,
  "is_locked": false,
  "is_eligible_for_decision": true,
  "requested_at": null,
  "processed_at": null,
  "notes": null
}
```

---

### 5. Pending periods (action required)

Periods where the month has ended and the client must choose cashout or store.

```http
GET /api/client/trading-periods/pending
```

Returns a **non-paginated** array in `data`.

Use this to drive a "Monthly decision" screen or push notifications.

---

### 6. Show trading period (with earnings)

```http
GET /api/client/trading-periods/{id}
```

Includes nested `trading_earnings` and `transaction` when available.

---

### 7. Request cashout (month-end)

```http
POST /api/client/trading-periods/{id}/request-cashout
```

**Body (JSON)**

```json
{
  "cashout_details_id": 2
}
```

| Field | Required | Description |
|-------|----------|-------------|
| `cashout_details_id` | No | Client's saved payout method (`GET /api/client/cashout-details`) |

**Success:** `201` with `TradingTransaction` in `data`.

**Errors:** `422` if period is not eligible (`is_eligible_for_decision` is false).

---

### 8. Request store (month-end)

Keep earnings in the trading stored balance instead of cashing out.

```http
POST /api/client/trading-periods/{id}/request-store
```

No body required.

**Success:** `201` with `TradingTransaction` in `data`.

---

### 9. Trading periods chart data

```http
GET /api/client/trading-periods/chart
```

**Example response**

```json
{
  "status": "success",
  "data": {
    "labels": ["January 2026", "February 2026", "March 2026"],
    "earnings": [800, 1200.5, 950],
    "details": [
      {
        "label": "January 2026",
        "year": 2026,
        "month": 1,
        "total_earning": 800,
        "status": "cashed_out",
        "start_date": "2026-01-01",
        "end_date": "2026-01-31"
      }
    ]
  }
}
```

---

### 10. Trading cashout history

```http
GET /api/client/trading-cashouts
```

Paginated list of processed/pending trading cashouts.

---

### 11. Trading stored earnings history

```http
GET /api/client/trading-stored-earnings
```

**Meta includes:**

```json
{
  "stored_balance": 3450.00
}
```

---

### 12. Trading stored balance only

```http
GET /api/client/trading-stored-balance
```

```json
{
  "status": "success",
  "data": {
    "stored_balance": 3450.00
  }
}
```

---

## Recommended frontend flows

### A. Trading dashboard

1. `GET /trading-contracts?status=active` — show active contracts
2. `GET /trading-periods/pending` — badge / alert for decisions needed
3. `GET /trading-stored-balance` — show stored trading balance
4. `GET /trading-periods/chart` — line/bar chart of monthly earnings

### B. Contract detail screen

1. `GET /trading-contracts/{id}`
2. `GET /trading-earnings?trading_contract_id={id}`
3. `GET /trading-periods?trading_contract_id={id}`

### C. Month-end decision screen

1. `GET /trading-periods/{id}` — show `total_earning`, `period`, `is_eligible_for_decision`
2. If cashout: show cashout methods from `GET /cashout-details`
3. Submit:
   - `POST /trading-periods/{id}/request-cashout` with `{ "cashout_details_id": N }`
   - **or** `POST /trading-periods/{id}/request-store`
4. Poll or refresh until `status` is `cashed_out`, `stored`, or `rejected`

### D. History screens

- Cashouts: `GET /trading-cashouts`
- Stored: `GET /trading-stored-earnings`

---

## Eligibility rules (`is_eligible_for_decision`)

All must be true:

- `status === "completed"` (month has ended)
- `is_locked === false`
- `total_earning !== 0`

---

## Differences from mining API

| | Mining | Trading |
|---|--------|---------|
| Contracts | `GET /contracts` | `GET /trading-contracts` |
| Earnings | BTC + USD daily | USD entries only |
| Periods | 30-day earning periods | Calendar months |
| Store at month end | Not allowed (cashout only) | **Allowed** |
| Currency | BTC + fiat | USD only |

Do **not** use mining endpoints for trading data.

---

## Admin processing

After the client submits cashout/store:

- Admin processes requests in **Trading → Trading Periods** in the admin panel.
- Client sees updated status via `GET /trading-periods/{id}` or list endpoints.

---

## Support

For new environments, run:

```bash
php artisan migrate
php artisan db:seed --class=PermissionSeeder
```

Admin users need permissions: `view_trading_contracts`, `view_trading_earnings`, `view_trading_periods`.
