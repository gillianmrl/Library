# Library Management API

This project is a **Library Management API** built using the Slim Framework. It provides core functionalities for managing users, authors, books, and authentication, offering secure access via JWT (JSON Web Token).

Created by **Marilao, Gillian Mae C.** from **4-D**.

---

## Table of Contents
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Endpoints Overview](#endpoints-overview)
- [Endpoint Breakdown](#detailed-endpoint-breakdown)
- [Installation](#installation)
---

## Features
- **User Management**
  - Register and log in as a user
  - View user account details
- **Author Management**
  - Register and log in as an author
  - Post and manage books
- **Book and Author Search**
  - Search and view books and authors
- **Authentication**
  - Secure JWT-based authentication

---

## Technology Stack
- **Backend Framework**: [Slim Framework](https://www.slimframework.com/)
- **Database**: MySQL
- **Authentication**: JWT (JSON Web Token)
- **Development Environment**: XAMPP
- **Programming Languages**: PHP, JavaScript

---

## Endpoints Overview

### User Endpoints
| Endpoint           | Method | Description                         |
|--------------------|--------|-------------------------------------|
| `/user/reg`        | POST   | Register a new user                |
| `/user/login`      | POST   | Log in a user                      |
| `/user/auth`       | POST   | Authenticate the user              |
| `/user/acc`        | POST   | View user account details          |

### Author Endpoints
| Endpoint           | Method | Description                         |
|--------------------|--------|-------------------------------------|
| `/author/reg`      | POST   | Register a new author              |
| `/author/login`    | POST   | Log in an author                   |
| `/author/auth`     | POST   | Authenticate the author            |
| `/author/postBook` | POST   | Post a new book by the author      |
| `/books/viewBooks` | POST   | View books posted by the logged-in author |

### Public Endpoints
| Endpoint               | Method | Description                       |
|------------------------|--------|-----------------------------------|
| `/viewAuthor`          | POST   | View all authors                 |
| `/books-authorView`    | POST   | View a specific author and books |

***

# Endpoint Breakdown
## User Endpoints
### 1. User Registration (`/user/reg`)
- **Method**: `POST`
- **Description**: This endpoint allows a new user to register by providing a username and password. It checks if the username is already taken and, if not, inserts the new user into the database with the hashed password.
  
#### Request Body:
```json
{
  "username": "gillian",
  "password": "123"
}
```

#### Response: 
##### Success Response (when the registration is successful):
```json
{
  "status": "Success",
  "data": null
}
```

##### Failure Response (if username already exists):
```json
{
  "status": "Failed",
  "data": {
    "title": "Username already exists"
  }
}
```
### 2. User Login (`user/login`)
- **Method**: `POST`
- **Description**: This endpoint allows a user to log in by providing their registered username and password. Upon successful authentication, the user receives a JWT (JSON Web Token) for accessing protected resources.

#### Request Body: 
```json
{
  "username": "gillian",
  "password": "123"
}
```

#### Response: 
##### Success Response: 
```json
{
  "status": "Success",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc2VjdXJpdHkub3JnIiwiYXVkIjoiaHR0cDovL3NlY3VyaXR5LmNvbSIsImlhdCI6MTczMjQ1OTU1MSwiZXhwIjoxNzMyNDU5NjExLCJkYXRhIjp7Im5hbWUiOiJnaWxsIn19.GLSx5GMK7pZekm5QRzeuJDp-chzVO7hWvz8EI1ptcR4"
  }
}
```

##### Failure Response: 
```json
{
  "status": "Failed",
  "data": {
    "title": "Invalid credentials"
  }
}
```

### User Authentication (`/user/auth`) 
- **Method**: `POST`
- **Description**: This endpoint is used to authenticate the user by verifying the JWT token provided in the request header. This helps to confirm that the user is authorized to access protected resources.

#### Request Body: 
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc2VjdXJpdHkub3JnIiwiYXVkIjoiaHR0cDovL3NlY3VyaXR5LmNvbSIsImlhdCI6MTczMTI5Mzk1MiwiZXhwIjoxNzMxMjk0MDEyLCJkYXRhIjp7Im5hbWUiOiJnaWxsIn19.W-Ow_v_s-qAwDvrzXPp4O-QdnuXgCNmeDSP-k8eekWc"
}
```
##### Success Response: 
```json
{
  "status": "Success",
  "data": {
    "message": "User authenticated",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc2VjdXJpdHkub3JnIiwiYXVkIjoiaHR0cDovL3NlY3VyaXR5LmNvbSIsImlhdCI6MTczMjQ1OTg2MCwiZXhwIjoxNzMyNDU5OTIwLCJkYXRhIjp7Im5hbWUiOiJnaWxsIn19.u8HOINo0r1Jz7aOVj9TiDjuwYQWgZsEXo8RRJZx84-4"
  }
}
```

##### Failure Response: 
```json
{
  "status": "Failed",
  "data": {
    "title": "Invalid or expired token"
  }
}
```

### User Account (`/user/acc`)
- **Method**: `POST`
- **Description**: This endpoint is used to view the user's account information. It requires the user to be authenticated via a valid JWT token from the (`/user/auth`) endpoint

#### Request Body: 
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc2VjdXJpdHkub3JnIiwiYXVkIjoiaHR0cDovL3NlY3VyaXR5LmNvbSIsImlhdCI6MTczMTI5Mzk2MSwiZXhwIjoxNzMxMjk0MDIxLCJkYXRhIjp7Im5hbWUiOiJnaWxsIn19.QKx27yhdl5HpPWieXZfpV5Qi953y3yDzFPObJw34OpU"
}
```

##### Success Response: 
```json
{
  "status": "Success",
  "data": {
    "username": "gill",
    "password": "a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3"
  }
}
```

##### Failure Response: 
```json
{
  "status": "Failed",
  "data": {
    "title": "Invalid or unauthorized token"
  }
}
```

## Author Endpoints
### 1. Author Registration (`/author/reg`)
- **Method**: `POST`
- **Description**: This endpoint allows an author to register by providing the necessary details such as username and password. Upon successful registration, the author can log in to the system.
  
#### Request Body: 
```json
{
  "username": "mama", 
  "password": 123
}
```

#### Response: 
##### Success Response: 
```json
{
  "username": "mama", 
  "password": 123
}
```

##### Failure Response: 
```json
{
  "status": "Failed",
  "data": {
    "title": "Username of Author already exists"
  }
}
```

### 2. Author Login (`/author/login`)
- **Method**: `POST`
- **Description**: This endpoint allows authors to log in by providing their username and password. If the credentials are valid, the server will return a JWT token that can be used for subsequent authentication in other protected endpoints.

#### Request Body: 
```json
{
  "username": "mama", 
  "password": 123
}
```

#### Response: 
##### Success Response: 
```json
{
  "status": "Success",
  "data": {
    "message": "Author logged in successfully",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc2VjdXJpdHkub3JnIiwiYXVkIjoiaHR0cDovL3NlY3VyaXR5LmNvbSIsImlhdCI6MTczMjQ2MDUzNiwiZXhwIjoxNzMyNDYwNTk2LCJkYXRhIjp7Im5hbWUiOiJtYW1hIn19.v2h6X8kgCl0-nGpWpmFapGKXmv8qvaWDoUnjoIG5wi4"
  }
}
```

##### Failure Response: 
```json
{
  "status": "Failed",
  "data": {
    "title": "Invalid credentials"
  }
}
```

## 3. Author Authentication (`/author/auth`)
- **Method**: `POST`
- **Description**: This endpoint is used to authenticate an author by verifying the JWT token provided in the request. It ensures that the author is authorized to perform actions that require authentication.

### Request Body: 
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc2VjdXJpdHkub3JnIiwiYXVkIjoiaHR0cDovL3NlY3VyaXR5LmNvbSIsImlhdCI6MTczMjQ2MDczNiwiZXhwIjoxNzMyNDYwNzk2LCJkYXRhIjp7Im5hbWUiOiJtYW1hIn19.cSLYJPbG3N6zn1SQjWbuY1ZQ1LhzJnDq7D8ETWsfjZ8"
}
```

### Response: 
#### Success Response: 
```json
{
  "status": "Success",
  "data": {
    "message": "Author authenticated successfully",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc2VjdXJpdHkub3JnIiwiYXVkIjoiaHR0cDovL3NlY3VyaXR5LmNvbSIsImlhdCI6MTczMjQ2MDc1MCwiZXhwIjoxNzMyNDYwODEwLCJkYXRhIjp7Im5hbWUiOiJtYW1hIn19.eiDTpnjvm8jczVCSq2EVq4z_lwMbbSU7Aq2be3R9z58"
  }
}
```

#### Failure Response: 
```json
{
  "status": "Failed",
  "data": {
    "title": "Invalid or expired token"
  }
}
```

## 4. Author PostBook (`/author/postBook`)
- **Method**: `POST`
- **Description**: This endpoint allows an authenticated author to post a new book to the system. The author must provide the book details such as the title. The request must include a valid JWT token for authentication.

### Request Body: 
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc2VjdXJpdHkub3JnIiwiYXVkIjoiaHR0cDovL3NlY3VyaXR5LmNvbSIsImlhdCI6MTczMjQ2MDk3MiwiZXhwIjoxNzMyNDYxMDMyLCJkYXRhIjp7Im5hbWUiOiJtYW1hIn19.rYsDyQirQTctvvfce5OdDJ1Tid6Sq1JuPqdPZmVjHsI", 
  "title": "red"
}
```

### Response: 
#### Success Response: 
```json
{
  "status": "Success",
  "data": {
    "message": "Book posted successfully"
  }
}
```

#### Failure Response: 
```json
{
  "status": "Failed",
  "data": {
    "title": "Token invalid or expired"
  }
}
```

## Author ViewBook (`/books/viewBooks`)
- **Method**: `POST`
- **Description**: This endpoint allows an authenticated author to view the list of books they have posted. The author must be logged in and provide a valid JWT token for authentication. The response will include all books that have been posted by the logged-in author.

### Request Body: 
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc2VjdXJpdHkub3JnIiwiYXVkIjoiaHR0cDovL3NlY3VyaXR5LmNvbSIsImlhdCI6MTczMjQ2MTE2NSwiZXhwIjoxNzMyNDYxMjI1LCJkYXRhIjp7Im5hbWUiOiJtYW1hIn19.vLYMYjSNMy-vEzqreDvyElEbDL5Mh0FyHfw6wTCDZK8"
}
```

### Response: 
#### Success Response: 
```json
{
  "status": "Success",
  "data": [
    {
      "bookid": "50",
      "title": "red"
    }
  ]
}
```

#### Failure Response: 
```json
{
  "status": "Failed",
  "data": {
    "title": "Token invalid or expired"
  }
}
```

## User-End Author Endpoint
### 1. User view Author (`/viewAuthor`)
- **Method**: `POST`
- **Description**: This endpoint allows a user to view a list of all authors. It retrieves details of all authors in the system including authorname and authorid.

#### Request Body: 
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc2VjdXJpdHkub3JnIiwiYXVkIjoiaHR0cDovL3NlY3VyaXR5LmNvbSIsImlhdCI6MTczMjQ2MTQ1OCwiZXhwIjoxNzMyNDYxNTE4LCJkYXRhIjp7Im5hbWUiOiJtYW1hIn19.nBx2BxqYHH29tqkqVa6xi35p5TgWP6_L2rgRb42IsqE"
}
```

#### Response: 
##### Success Response: 
```json
{
  "status": "Success",
  "data": [
    {
      "authorid": "25",
      "username": "baba"
    },
    {
      "authorid": "26",
      "username": "nana"
    },
    {
      "authorid": "27",
      "username": "lala"
    },
    {
      "authorid": "28",
      "username": "mama"
    }
  ]
}
```

##### Failure Response: 
```json
{
  "status": "Failed",
  "data": {
    "title": "Token invalid or expired"
  }
}
```

## User-End Book and Author Endpoint
### 1. User view Book and Author
- **Method**: `POST`
- **Description**: This endpoint allows a user to view specific book details and specific author details separately. The user provides the bookid to view the details of a specific book and the authorid to view the details of a specific author. A valid JWT token is required for authentication.

#### Request Body: 
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc2VjdXJpdHkub3JnIiwiYXVkIjoiaHR0cDovL3NlY3VyaXR5LmNvbSIsImlhdCI6MTczMTI5NDExOCwiZXhwIjoxNzMxMjk0MTc4LCJkYXRhIjp7Im5hbWUiOiJsYWxhIn19.JEjZv4C39aNAwRqnNo0idrocjHHadjwpOQCvu1xffSY",
  "bookid": 34,
  "authorid": 27
}
```

#### Response: 
##### Success Response: 
```json
{
  "status": "Success",
  "data": {
    "author": {
      "id": "27",
      "username": "lala"
    },
    "book": {
      "id": "34",
      "title": "banana"
    }
  }
}
```

##### Failure Response: 
```json
{
  "status": "Failed",
  "message": "Connection failed: Expired token"
}
```





 



