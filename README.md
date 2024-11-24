# Library Management API

This project is a Library Management API built using the Slim Framework. It enables the management of users, authors, and their respective functionalities, such as registration, login, authentication, and book postings. The API employs JWT (JSON Web Token) for authentication and token management. 

***

# Features
* **User Management:**
  * Register and log in as a user
  * View user accounts
* **Author Management:**
  * Register and log in as an author
  * Post books as an author
* **Books and Authors:**
  * Search and view books and authors
* **Authentication:**
  * Secure authentication using JWT

***

# Endpoints

## User Endpoints
  * `POST /user/reg` - Register a new user
  * `POST /user/login` - Log in a user
  * `POST /user/auth` - Authenticate the user
  * `POST /user/acc` - View user account information

## Author Endpoints
  * `POST /author/reg` - Register a new author
  * `POST /author/login` - Log in an author
  * `POST /author/auth` - Authenticate the author
  * `POST /author/postBook` - Post a new book by the author
  * `POST /books/viewBooks` - View the books posted by the logged-in author

## User-End Author Endpoints
  * `POST /viewAuthor` - View all authors registered in the system

## User-End Book and Author Endpoints
  * `POST /books-authorView` - View a specific author's details and their posted books

***

# Endpoint Breakdown
## 1. User Registration (`/user/reg`)
- **Method**: `POST`
- **Description**: This endpoint allows a new user to register by providing a username and password. It checks if the username is already taken and, if not, inserts the new user into the database with the hashed password.
  
### Request Body:
```json
{
  "username": "gillian",
  "password": "123"
}
```

### Response: 
* Success Response (when the registration is successful):
```json
{
  "status": "Success",
  "data": null
}
```

* Failure Response (if username already exists):
```json
{
  "status": "Failed",
  "data": {
    "title": "Username already exists"
  }
}
```
## 2. User Login (`user/login`)
- **Method**: `POST`
- **Description**: This endpoint allows a user to log in by providing their registered username and password. Upon successful authentication, the user receives a JWT (JSON Web Token) for accessing protected resources.

### Request Body: 
```json
{
  "username": "gillian",
  "password": "123"
}
```

### Response: 
 



