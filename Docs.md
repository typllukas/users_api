
#  Dokumentace USERS API

Vítejte v **API pro správu uživatelů**. Toto API poskytuje sadu endpointů pro pro práci s funkcemi, jako je načítání informací o uživatelích, vytváření nových uživatelů a jejich úprava, mazání či výpis záznamu provedených operací.

**Základní URL:** `/api`  
**Autentizace:** JWT Bearer Token 
**Content-Type:** `application/json`  

---

## Přehled Endpointů

###  Autentizace
| Metoda | Endpoint    | Popis                      | 
|--------|-------------|----------------------------|
| POST   | `/login` | přihlášení a získání JWT   | 

###  Uživatelé
| Metoda | Endpoint                  | Popis                             |
|--------|---------------------------|-----------------------------------|
| GET    | `/users/me`           | informace o přihlášeném uživateli |
| GET    | `/users`   | získání uživatele podle ID/emailu (`?id=123` nebo `?email=user@example.com`) |            
| GET    | `/users/list`          | výpis uživatelů s volitelnými filtry          |             
| POST   | `/users`              | vytvoření nového uživatele        |          
| PUT    | `/users/{id}`         | aktualizace uživatele podle ID    |             
| DELETE | `/users/{id}`         | smazání uživatele (soft/hard)     |             

###  Audit log
| Metoda | Endpoint      | Popis                         | 
|--------|----------------|------------------------------|
| GET    | `/audit`   | výpis logů provedených operací s volitelnými filtry | 
---

##  Detailní popis Endpointů
---
###  Přihlášení uživatele
Autentizace uživatele a získání JWT tokenu pro přístup k ostatním endpointům.

- **URL:** `/api/login`  
- **Metoda:** `POST`  

#### Tělo požadavku
Tělo požadavku musí obsahovat následující povinné klíče a hodnoty:

```json
{
  "email": "uzivatel@example.com",
  "password": "bezpecneHeslo123*"
}
```

#### Úspěšná odpověď
```json
{
  "token": "vas.jwt.token.zde"
}
```

#### Chybová odpověď
```json
{
  "error": "Invalid credentials."
}
```

---

###  Informace o přihlášeném uživateli
Umožňuje získat uživateli své vlastní údaje.

- **URL:** `/api/users/me`  
- **Metoda:** `GET`  
- **Požadovaná role** `user` 

#### Parametry

Kromě JWT tokenu v hlavičce nevyžaduje tento endpoint žádné parametry.

#### Úspěšná odpověď
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "Test User 1",
        "email": "testuser1@example.com",
        "role": "user",
        "created_at": "2025-02-23 19:10:31",
        "updated_at": null,
        "deleted_at": null
    }
}
```

---


###  Načtení uživatele podle ID nebo e-mailu
Získání detailních informací o uživateli.

- **URL:** `/api/users`  
- **Metoda:** `GET`  
- **Požadovaná role** `admin` 

#### Parametry 
Je třeba uvést **jeden z** následujících parametrů.
- `id`  — ID uživatele  
- `email`  — E-mail uživatele  

#### Příklad
```
GET /api/users?email=testadmin1@example.com
```

#### Úspěšná odpověď
```json
{
    "status": "success",
    "data": {
        "id": 2,
        "name": "Test Admin 1",
        "email": "testadmin1@example.com",
        "role": "admin",
        "created_at": "2025-02-23 19:10:31",
        "updated_at": null,
        "deleted_at": null
    }
}
```
---

###  Výpis uživatelů
Získání seznamu uživatelů s možností filtrování.

- **URL:** `/api/users/list`  
- **Metoda:** `GET`  
- **Požadovaná role** `user` 
#### Volitelné parametry
| Parametr          | Povolené hodnoty                             | Defaultní hodnota v případě neuvedení     |
|-------------------|-----------------------------------------------|-------------------------------------------|
| `role`            | `user`, `admin`                               | -                                    |
| `created_after`   | datum v ISO formátu (e.g., `2022-01-01`)               | -                                         |
| `created_before`  | datum v ISO formátu (e.g., `2022-01-01`)               | -                                         |
| `include_deleted` | `true`, `false`                               | `false`                                   |
| `only_deleted`    | `true`, `false`                               | `false`                                   |

#### Příklad
```
GET /api/users/list?role=admin&include_deleted=true
```

#### Úspěšná odpověď
```json
{
    "status": "success",
    "data": {
        "users": [
            {
                "id": 2,
                "name": "Test Admin 1",
                "email": "testadmin1@example.com",
                "role": "admin",
                "created_at": "2025-02-23 19:10:31",
                "updated_at": null,
                "deleted_at": null
            },
            {
                "id": 3,
                "name": "Test Admin 2",
                "email": "testadmin2@example.com",
                "role": "admin",
                "created_at": "2025-02-24 11:39:23",
                "updated_at": "2025-02-24 15:12:39",
                "deleted_at": "2025-02-24 15:12:39"
            }
        ]
    }
}
```

---

### Vytvoření nového uživatele

- **URL:** `/api/users`  
- **Metoda:** `POST`  
- **Požadovaná role** `admin` 

#### Tělo požadavku
Tělo požadavku musí obsahovat všechny následující klíče a hodnoty. Povolené role jsou `user`a `admin`.
```json
{
"name": "Test Admin 5",
"email":"testadmin5@example.com",
"password":"testAdmin5*",
"role": "admin"
}
```

#### Úspěšná odpověď
```json
{
    "status": "success",
    "data": {
        "status": "User created",
        "user": {
            "id": 10,
            "name": "Test Admin 5",
            "email": "testadmin5@example.com",
            "role": "admin",
            "created_at": "2025-02-24 16:47:22",
            "updated_at": null,
            "deleted_at": null
        }
    }
}
```

---

###  Aktualizace údajů uživatele


- **URL:** `/api/users/{id}`  
- **Metoda:** `PUT`  
- **Požadovaná role** `admin` 

#### Tělo požadavku
Tělo požadavku musí obsahovat alespoň jeden z následujících párů klíč-hodnota.  Povolené role jsou `user`a `admin`.
```json
{
"name": "New name",
"email":"newemail@example.com",
"password":"newpassword",
"role": "user"
}
```

#### Úspěšná odpověď
```json
{
    "status": "success",
    "data": {
        "status": "User with ID 2 updated",
        "user": {
            "id": 2,
            "name": "New name",
            "email": "newemail@example.com",
            "role": "admin",
            "created_at": "2025-02-23 19:10:31",
            "updated_at": "2025-02-24 16:52:28",
            "deleted_at": null
        }
    }
}
```

---

### Smazání uživatele

- **URL:** `/api/users/{id}`  
- **Metoda:** `DELETE`  
- **Požadovaná role** `admin` 

#### Příklad
```
DELETE /api/users/3?hard_delete=true
```

#### Volitelné parametry
| Parametr          | Povolené hodnoty                             | Defaultní hodnota v případě neuvedení     | Popis                                                                                           |
|-------------------|-----------------------------------------------|-------------------------------------------|-----------|
| `hard_delete`            | `true`, `false`                              | `false `                                   | V případě nastavení na `true` dojde k trvalému odstranění záznamu z databáze, nikoliv jen k označení uživatele jako smazaného.



#### Úspěšná odpověď
```json
{
    "status": "success",
    "data": {
        "status": "User deleted"
    }
}
```

---

###  Výpis záznamů operací

- **URL:** `/api/audit`  
- **Metoda:** `GET`  
- **Požadovaná role** `admin` 

#### Volitelné parametry
| Parametr          | Povolené hodnoty                              |
|-------------------|-----------------------------------------------|
| `action`            | `create`, `update`, `hard_delete`, `soft_delete`                           |                                |
| `performed_by_id`   | id uživatele, který operaci provedl         |                                        |
| `target_user_id`  | id uživatele, kterého se operace týkala          |                                          |
| `created_after` | datum v ISO formátu (e.g., `2022-01-01`)                             |                                 |
| `created_before`    | datum v ISO formátu (e.g., `2022-01-01`)                               |                                    |


#### Příklad požadavku
```
GET /api/audit?action=update&created_after=2024-01-01
```

#### Úspěšná odpověď
```json
{
    "status": "success",
    "data": {
        "logs": [
            {
                "action": "update",
                "performed_by_id": 2,
                "target_user_id": 7,
                "changed_data": {
                    "name": {
                        "new": "Test Admin 4 Update",
                        "old": "Test Admin 4"
                    }
                },
                "log_created_at": "2025-02-24 12:41:41"
            }
        ]
    }
}
```

---

## Chybové odpovědi

V JSON odpovědi naleznete chybový kód i detailní popis chyby.

| **HTTP Code** | **Description**                      |
|---------------|--------------------------------------|
| 400           | Bad Request                          |
| 401           | Unauthorized                         |
| 403           | Forbidden                            |
| 404           | Not Found                            |
| 409           | Conflict                             |
| 500           | Internal Server Error                |

### Příklady chybových hlášek


#### Invalid JSON Format
```json
{
  "error": "Invalid JSON format. Please provide JSON object."
}
```


#### Invalid Email Format
```json
{
  "error": "Invalid e-mail format"
}
```

#### Exclusive parameters
```json
{
  "error": "Specify either id or email, not both."
}
```

#### Missing Required Fields
```json
{
  "error": "The following fields are mandatory: name, email, password, role"
}
```
#### Invalid Values
```json
{
  "error": "Invalid role 'example_role'. Allowed roles are: admin, user, guest"
}
```
#### Invalid Fields
```json
{
  "error": "Invalid fields provided for update.",
  "details": {
    "invalid_fields": ["unknown_field"],
    "allowed_fields": ["name", "email", "password", "role"]
  }
}
```

#### Invalid Date Range
```json
{
  "error": "created_after cannot be later than created_before."
}
```
#### Database data-related errors
```json
{
  "error": "User is already soft-deleted."
}
```

```json
{
  "error": "User not found."
}
```



