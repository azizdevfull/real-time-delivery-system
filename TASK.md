# 🧱 1. SYSTEM DIAGRAM 

```text
                ┌───────────────┐
                │   User / App  │
                └──────┬────────┘
                       │ HTTP
                       ▼
                ┌───────────────┐
                │ Order Service │
                └──────┬────────┘
                       │ order.created
                       ▼
              ┌────────────────────┐
              │  RabbitMQ Exchange │
              │   delivery_events  │
              └──────┬───────┬────┘
                     │       │
        ┌────────────┘       └─────────────┐
        ▼                                  ▼
┌───────────────┐                 ┌────────────────┐
│ Dispatch Svc  │                 │ Driver Service │
└──────┬────────┘                 └──────┬─────────┘
       │ order.assigned                 │ driver.accepted
       ▼                                ▼
       └──────────────┬─────────────────┘
                      ▼
               ┌───────────────┐
               │ Order Service │
               └───────────────┘

Driver harakat qiladi:
Driver → driver.location.updated → Exchange → Tracking Service
                                                │
                                                ▼
                                           Redis (real-time)
```

---

# ⚙️ 2. EXCHANGE & QUEUE STRUCTURE

## 🎯 1 ta exchange (MVP uchun yetadi)

```
exchange: delivery_events (type: topic)
```

---

## 📬 Queue lar:

```text
order_queue
dispatch_queue
driver_queue
tracking_queue
```

---

## 🔗 Binding (ENG MUHIM)

```text
order_queue     ← order.*
dispatch_queue  ← order.created
driver_queue    ← order.assigned
tracking_queue  ← driver.location.updated
```

---

# 🧠 3. EVENT FLOW (aniq va ketma-ket)

---

## 📦 1. Order yaratiladi

```
Order Service
→ publish: order.created
→ exchange: delivery_events
```

---

## 🚦 2. Dispatch ishlaydi

```
Dispatch Service
(listen: order.created)

→ driver tanlaydi
→ publish: order.assigned
```

---

## 🚗 3. Driver accept qiladi

```
Driver Service
(listen: order.assigned)

→ publish: driver.accepted
```

---

## 🔄 4. Order update

```
Order Service
(listen: driver.accepted)

→ status = delivering
```

---

## 📍 5. Location stream (loop 🔥)

```
Driver Service
→ publish: driver.location.updated (har 1-2s)

Exchange
→ Tracking Service
→ Redis ga yozadi
```

---

# 🧾 4. EVENT PAYLOAD (minimal, standart)

---

## order.created

```json
{
  "order_id": 1,
  "user_id": 10,
  "pickup": "A",
  "dropoff": "B"
}
```

---

## order.assigned

```json
{
  "order_id": 1,
  "driver_id": 5
}
```

---

## driver.accepted

```json
{
  "order_id": 1,
  "driver_id": 5
}
```

---

## driver.location.updated

```json
{
  "driver_id": 5,
  "lat": 41.3111,
  "lng": 69.2797
}
```

---

# 🧱 5. SERVICE BREAKDOWN (aniq)

---

## 📦 Order Service

* POST /orders
* emit: `order.created`
* listen:

  * `order.assigned`
  * `driver.accepted`

---

## 🚦 Dispatch Service

* listen: `order.created`
* emit: `order.assigned`

---

## 🚗 Driver Service

* POST /accept
* POST /location
* emit:

  * `driver.accepted`
  * `driver.location.updated`

---

## 📍 Tracking Service

* listen: `driver.location.updated`
* Redis ga yozadi

---

# 🔥 6. ENG MUHIM QOIDALAR

---

## ❗ 1. Service’lar bir-biriga tegmaydi

* ❌ direct API yo‘q
* ✅ faqat event

---

## ❗ 2. Har event idempotent bo‘lishi kerak

* duplicate kelishi mumkin

---

## ❗ 3. Location DB ga yozilmaydi (har safar)

* Redis only (real-time)

---

# 🎯 Yakuniy ko‘rinish (short mental model)

```text
Order → Dispatch → Driver → Tracking
         ↓          ↓
      RabbitMQ (event bus)
```

---
To‘g‘ri qaror Azizbek aka 💯🔥
Minimal model — tezlik, fokus, natija. Ortiqcha columnlar → keraksiz complexity.

Quyida sizga **MVP uchun minimal schema** beraman. Bu bilan system ishlaydi, keyin kengaytirasiz.

---

# 🧱 1. ORDER SERVICE

## 📦 Model: `Order`

### 🎯 Maqsad:

Faqat lifecycle boshqarish

---

## 🗄 Migration:

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->unsignedBigInteger('driver_id')->nullable();
    $table->string('status'); // pending | assigned | delivering | done
    $table->timestamps();
});
```

---

## 💡 Nima uchun yetarli?

* `user_id` → kim buyurtma berdi
* `driver_id` → kimga berildi
* `status` → flow control

👉 boshqa hamma narsa event’da

---

# 🚗 2. DRIVER SERVICE

## 📦 Model: `Driver`

---

## 🗄 Migration:

```php
Schema::create('drivers', function (Blueprint $table) {
    $table->id();
    $table->string('status'); // available | busy
    $table->timestamps();
});
```

---

## 💡 Nima uchun minimal?

* `status` yetarli:

  * available → dispatch oladi
  * busy → ignore

👉 location DB’da yo‘q ❗

---

# 📍 3. TRACKING SERVICE

## ❗ DB shart emas (MVP)

---

## 🔥 Real-time:

👉 Redis:

```
driver:{id}:location
```

---

## Agar history xohlasangiz (optional):

```php
Schema::create('driver_locations', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('driver_id');
    $table->float('lat');
    $table->float('lng');
    $table->timestamps();
});
```

👉 lekin MVP’da SKIP qilsangiz ham bo‘ladi

---

# 🚦 4. DISPATCH SERVICE

## ❗ DB kerak emas

---

## 🎯 Nima qiladi?

* `order.created` keladi
* `drivers` service’dan (event yoki cache orqali) tanlaydi
* `order.assigned` yuboradi

---

👉 state saqlamaydi → stateless service

---

# 🧠 5. STATUS FLOW (IMPORTANT)

---

## Order status:

```text
pending → assigned → delivering → done
```

---

## Driver status:

```text
available → busy → available
```

---

# ⚡ 6. MINIMAL EVENT DATA (model bilan sync)

---

## order.created

```json
{
  "order_id": 1,
  "user_id": 10
}
```

---

## order.assigned

```json
{
  "order_id": 1,
  "driver_id": 5
}
```

---

## driver.accepted

```json
{
  "order_id": 1,
  "driver_id": 5
}
```

---

## driver.location.updated

```json
{
  "driver_id": 5,
  "lat": 41.31,
  "lng": 69.27
}
```
