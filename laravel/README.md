# Products

REST API + Web UI for product management

## Description

The application allows to authorize and manage products via web UI interface or REST API.

Only three entities are used:

1. User:
- name;
- email;
- password.

2. Product:
- title;
- price;
- currency.

3. Currency:
- code.

Both interfaces provide the following functionality:
- Auth:
  - registration;
  - email confirmation;
  - login;
  - password reset;
  - logout.
- Products:
  - list;
  - create;
  - veiw details;
  - update;
  - delete.

Products view and management are allowed for authorized users only.

## Tech stack

The application is developed with Laravel framework and comes with Docker environment.

## Install

1. Clone this repository to your project directory:
```
git clone https://github.com/mustakrakishe/products-2 products
```

2. Go to laravel directory:
```
cd products/laravel
```

3. Create an .env file:
```
cp .env.example .env
```

4. The .env already contains all requires preset values to start the application with Docker. However, you are free to change them.

5. Up the docker environment:
```
docker compose up -d
```

6. Enter to the php container:
```
docker compose exec php /bin/bash
```

7. install composer dependencies:
```
composer install
```

8. Run migrations:
```
php artisan migrate
```
If you want to add test data to database, add an "--seed" option:
```
php artisan migrate --seed
```
Also, you may add test data later with:
```
php artisan db:seed
```

9. Create an app secret key:
```
php artisan key:generate
```

10. Change ```storage``` directory owner to ```www-data```:
```
chown -R www-data:www-data storage
```
Done.

## Usage

Services are available now at: \
```http://localhost``` - Web UI; \
```http://localhost/api``` - REST API

Also in dev mode there is possible to run autotests:
```
php artisan test
```

## API usage examples

### Auth - Registration
> As this API method creates an uri an API client host, it is available for trusted client list, which is set at ```config/clients.php file```, only to prevent a host spoofing.

```
// Request

[POST] /api/register
{
  "name": "user",
  "email": "user@example.com",
  "password": "password",
  "password_confirmation": "password"
}

// Response

200: OK
{
  "message": "Follow the link at user@example.com to confirm your email."
}
```

### Auth - Email confirmation

> A link uri at email will lead to client's web ui. To verify the email you should do a get request to uri, which is contained at its "redirect" query parameter.

```
// Request

[GET] /api/auth/email/verify/{id}/{hash}?expires={expires}&signature={signature}

// Response

200: OK
{
  "data": {
    "id": 6,
    "name": "On register",
    "token": "6|tOUhDq3sSaQzaW70kPs4X6oqNfrWqf34NSgUNApMa08e6c05",
    "last_used_at": null,
    "expired_at": null,
    "created_at": "2024-03-29 07:35:03",
    "updated_at": "2024-03-29 07:35:03",
    "tokenable": {
      "id": 9,
      "name": "user",
      "email": "user@example.com",
      "email_verified_at": "2024-03-29 07:35:03",
      "created_at":"2024-03-29 07:00:28",
      "updated_at":"2024-03-29 07:35:03"
    }
  }
}
```

### Auth - Login

```
// Reuqest

[POST] /api/auth/login
{
  "email": "user@example.com",
  "password": "password"
}

// Response

200: OK
{
  "data": {
    "id": 7,
    "name": "On login",
    "token": "7|6JxUruMg4ErwqPWucd1UPw42H1bYJskA3i6NLv5D138a0b53",
    "last_used_at": null,
    "expired_at": null,
    "created_at": "2024-03-29 07:42:49",
    "updated_at": "2024-03-29 07:42:49",
    "tokenable": {
      "id": 9,
      "name": "user",
      "email": "user@example.com",
      "email_verified_at": "2024-03-29 07:35:03",
      "created_at": "2024-03-29 07:00:28",
      "updated_at": "2024-03-29 07:35:03"
    }
  }
}
```

### Auth - Send password reset link to email

> As this API method creates an uri an API client host, it is available for trusted client list, which is set at ```config/clients.php file```, only to prevent a host spoofing.

```
// Request

[POST] /api/auth/password/reset/send
{
  "email": "user@example.com"
}

// Response

200: OK
{
  "message": "We have emailed your password reset link."
}
```

### Auth - Reset password

> A link uri at email will lead to client's web ui. To reset the password you should include a token from its uri query parameter to an api reset password post request body.

```
// Request

[POST] /api/auth/password/reset
{
  "email": "user@example.com",
  "token": "126546836091fe7db0cb92e0a1fb4a12a71f80426216531d41278462ac3bd80d",
  "password": "password",
  "password_confirmation": "password"
}

// Response

200: OK
{
  "message": "Your password has been reset."
}
```

### Auth - Logout

```
// Request

[POST] /api/auth/logout

// Response

204: No Content
```

### Products - Get list

```
// Request

[GET] /api/products

// Response

200: OK
{
  "data": [
    {
      "id": 10,
      "title": "Ice Cream",
      "price": 153.95,
      "currency": {
        "id": 2,
        "code": "USD"
      },
      "created_at": "2024-03-27 06:47:33",
      "updated_at": "2024-03-27 06:47:33"
    },
    {
      "id": 9,
      "title": "Orange Juice",
      "price": 85.12,
      "currency": {
        "id": 1,
        "code": "UAH"
      },
      "created_at": "2024-03-27 06:47:33",
      "updated_at": "2024-03-27 06:47:33"
    },
    {
      "id": 8,
      "title": "Apple juice",
      "price": 48,
      "currency": {
        "id": 3,
        "code": "EUR"
      },
      "created_at": "2024-03-27 06:47:33",
      "updated_at": "2024-03-27 06:47:33"
    },
    {
      "id": 7,
      "title": "Pepsi",
      "price": 123.5,
      "currency": {
        "id": 2,
        "code": "USD"
      },
      "created_at": "2024-03-27 06:47:33",
      "updated_at": "2024-03-27 06:47:33"
    },
    {
      "id": 6,
      "title": "Solyanka",
      "price": 199.01,
      "currency": {
        "id": 1,
        "code": "UAH"
      },
      "created_at": "2024-03-27 06:47:32",
      "updated_at": "2024-03-27 06:47:32"
    },
    {
      "id": 5,
      "title": "Philadelphia roll",
      "price": 125.24,
      "currency": {
        "id": 1,
        "code": "UAH"
      },
      "created_at": "2024-03-27 06:47:32",
      "updated_at": "2024-03-27 06:47:32"
    },
    {
      "id": 4,
      "title": "Steak",
      "price": 193,
      "currency": {
        "id": 2,
        "code": "USD"
      },
      "created_at": "2024-03-27 06:47:32",
      "updated_at": "2024-03-27 06:47:32"
    }
  ],
  "links": {
    "first": "https://localhost/api/products?page=1",
    "last": "https://localhost/api/products?page=2",
    "prev": null,
    "next": "https://localhost/api/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 2,
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "https://localhost/api/products?page=1",
        "label": "1",
        "active": true
      },
      {
        "url": "https://localhost/api/products?page=2",
        "label": "2",
        "active": false
      },
      {
        "url": "https://localhost/api/products?page=2",
        "label": "Next &raquo;",
        "active": false
      }
    ],
    "path": "https://localhost/api/products",
    "per_page": 7,
    "to": 7,
    "total": 10
  }
}
```

### Products - Create new

```
// Request

[POST]   /api/products
{
  "title": "New Product",
  "price": 9.99,
  "currency_id": 1
}

// Response

201: Created
{
  "data": {
    "id": 11,
    "title": "New Product",
    "price": 9.99,
    "currency": {
      "id": 1,
      "code": "UAH"
    },
    "created_at": "2024-03-29 08:14:20",
    "updated_at": "2024-03-29 08:14:20"
  }
}
```

### Products - Show details

```
// Request

[GET] /api/products/{product}

// Response

200: OK
{
  "data": {
    "id": 1,
    "title": "Coca-cola",
    "price": 42.44,
    "currency": {
      "id": 1,
      "code": "UAH"
    },
    "created_at": "2024-03-27 06:47:32",
    "updated_at": "2024-03-27 06:47:32"
  }
}
```

### Products - Update

```
// Request

[PUT] /api/products/{product}
{
  "title": "Updated Product",
  "price": 55.55,
  "currency_id": 2
}

// Response

200: OK
{
  "data": {
    "id": 11,
    "title": "Updated Product",
    "price": 55.55,
    "currency": {
      "id": 2,
      "code": "USD"
    },
    "created_at": "2024-03-29 08:14:20",
    "updated_at": "2024-03-29 08:19:47"
  }
}
```

### Products - Delete

```
// Request

[DELETE] /api/products/{product}

// Response

204: No Content
```