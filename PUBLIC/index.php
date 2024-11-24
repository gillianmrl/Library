<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require '../src/vendor/autoload.php';

session_start(); 

$app = new \Slim\App;

function generateToken($data, $expire = 60) {
    $key = 'thisiskey';
    $payload = [
        'iss' => 'http://security.org',
        'aud' => 'http://security.com',
        'iat' => time(),
        'exp' => time() + $expire, 
        'data' => $data
    ];

    $jwt = JWT::encode($payload, $key, 'HS256');
    $_SESSION['token'] = $jwt; 
    return $jwt;
}


function validateAndConsumeToken($jwt) {
    $key = 'thisiskey';

    if (!isset($_SESSION['token']) || $_SESSION['token'] !== $jwt) {
        return false;
    }

    try {
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $currentTime = time();

        if ($decoded->exp < $currentTime) {
            unset($_SESSION['token']);
            return false; 
        }

        unset($_SESSION['token']); 
        return true; 
    } catch (Exception $e) {
        return false; 
    }
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library";
  

// user register
$app->post('/user/reg', function (Request $request, Response $response, array $args) use ($servername, $username, $password, $dbname) {
    $data = json_decode($request->getBody());
    $uname = $data->username;
    $pass = $data->password;

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if username already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :uname");
        $stmt->execute([':uname' => $uname]);
        if ($stmt->fetch()) {
            $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Username already exists"]]));
            return $response;
        }

        // Insert new user
        $sql = "INSERT INTO users (username, password) VALUES (:uname, :pass)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':uname' => $uname,
            ':pass' => hash('SHA256', $pass)
        ]);
        $response->getBody()->write(json_encode(["status" => "Success", "data" => null]));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => $e->getMessage()]]));
    }

    $conn = null;
    return $response;
});

// user login
$app->post('/user/login', function (Request $request, Response $response, array $args) use ($servername, $username, $password, $dbname) {
    $data = json_decode($request->getBody());
    $uname = $data->username;
    $pass = $data->password;

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Authenticate user
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :uname AND password = :pass");
        $stmt->execute([
            ':uname' => $uname,
            ':pass' => hash('SHA256', $pass)
        ]);

        if ($stmt->fetch()) {
            // Generate JWT token upon successful login
            $jwt = generateToken(["name" => $uname]);
            $_SESSION['login_token'] = $jwt;  // Store the login token in the session
            $response->getBody()->write(json_encode(["status" => "Success", "data" => ["token" => $jwt]]));
        } else {
            $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Invalid credentials"]]));
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => $e->getMessage()]]));
    }

    $conn = null;
    return $response;
});

// user authentication
$app->post('/user/auth', function (Request $request, Response $response, array $args) use ($servername, $username, $password, $dbname) {
    $data = json_decode($request->getBody());
    $token = $data->token;

    if (!isset($token)) {
        return $response->withStatus(400)->write(json_encode(["status" => "Failed", "data" => ["title" => "Token is required"]]));
    }

    try {
        // Validate the JWT token
        $decoded = JWT::decode($token, new Key('thisiskey', 'HS256'));
        $usernameFromToken = $decoded->data->name;

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verify that the user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :uname");
        $stmt->execute([':uname' => $usernameFromToken]);

        if ($stmt->fetch()) {
            // Generate a new token upon successful authentication
            $newJwt = generateToken(["name" => $usernameFromToken]);
            $_SESSION['auth_token'] = $newJwt;  // Store the new token in the session
            
            $response->getBody()->write(json_encode(["status" => "Success", "data" => ["message" => "User authenticated", "token" => $newJwt]]));
        } else {
            $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Authentication failed"]]));
        }
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Invalid or expired token"]]));
    }

    $conn = null;
    return $response;
});

// user account
$app->post('/user/acc', function (Request $request, Response $response, array $args) use ($servername, $username, $password, $dbname) {
    $data = json_decode($request->getBody());

    // Check if the token is provided
    if (!isset($data->token)) {
        return $response->withStatus(400)->write(json_encode(["status" => "Failed", "data" => ["title" => "Token is required"]]));
    }

    $jwt = $data->token;

    // Validate the authentication token stored in the session
    if (!isset($_SESSION['auth_token']) || $_SESSION['auth_token'] !== $jwt) {
        return $response->withStatus(401)->write(json_encode(["status" => "Failed", "data" => ["title" => "Invalid or unauthorized token"]]));
    }

    // Decode the JWT to get the username
    try {
        $decoded = JWT::decode($jwt, new Key('thisiskey', 'HS256'));
        $usernameFromToken = $decoded->data->name;

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Retrieve employee details from the database
        $stmt = $conn->prepare("SELECT username, password FROM users WHERE username = :username");
        $stmt->execute([':username' => $usernameFromToken]);

        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($employee) {
            $response->getBody()->write(json_encode(["status" => "Success", "data" => $employee]));
        } else {
            $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Employee not found"]]));
        }
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Token invalid or expired"]]));
    }

    return $response;
});

//author reg
$app->post('/author/reg', function (Request $request, Response $response, array $args) use ($servername, $username, $password, $dbname) {
    $data = json_decode($request->getBody());
    $uname = $data->username;
    $pass = $data->password;

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if username already exists
        $stmt = $conn->prepare("SELECT * FROM authors WHERE username = :uname");
        $stmt->execute([':uname' => $uname]);
        if ($stmt->fetch()) {
            $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Username of Author already exists"]]));
            return $response;
        }

        // Insert new author without specifying authorid (assuming it's auto-incremented)
        $sql = "INSERT INTO authors (username, password) VALUES (:uname, :pass)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':uname' => $uname,
            ':pass' => hash('SHA256', $pass)
        ]);

        $response->getBody()->write(json_encode(["status" => "Success", "data" => null]));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => $e->getMessage()]]));
    }

    $conn = null;
    return $response;
});

//author login
$app->post('/author/login', function (Request $request, Response $response, array $args) use ($servername, $username, $password, $dbname) {
    $data = json_decode($request->getBody());
    $uname = $data->username;
    $pass = $data->password;

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Authenticate author
        $stmt = $conn->prepare("SELECT * FROM authors WHERE username = :uname AND password = :pass");
        $stmt->execute([
            ':uname' => $uname,
            ':pass' => hash('SHA256', $pass)
        ]);

        if ($stmt->fetch()) {
            $jwt = generateToken(["name" => $uname]); // Generate JWT token
            $_SESSION['auth_token'] = $jwt; // Store token in session   
            $response->getBody()->write(json_encode(["status" => "Success", "data" => ["message" => "Author logged in successfully", "token" => $jwt]]));
        } else {
            $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Invalid credentials"]]));
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => $e->getMessage()]]));
    }

    $conn = null;
    return $response;
});

// author authen
$app->post('/author/auth', function (Request $request, Response $response, array $args) use ($servername, $username, $password, $dbname) {
    $data = json_decode($request->getBody());
    $token = $data->token;

    if (!isset($token)) {
        return $response->withStatus(400)->write(json_encode(["status" => "fail", "data" => ["title" => "Token is required"]]));
    }

    try {
        // Validate the JWT token
        $decoded = JWT::decode($token, new Key('thisiskey', 'HS256'));
        $usernameFromToken = $decoded->data->name;

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verify that the author exists using the username from the token
        $stmt = $conn->prepare("SELECT * FROM authors WHERE username = :uname");
        $stmt->execute([':uname' => $usernameFromToken]);

        if ($stmt->fetch()) {
            // Generate a new token upon successful authentication
            $newJwt = generateToken(["name" => $usernameFromToken]);
            $_SESSION['auth_token'] = $newJwt; // Store the new token in the session

            $response->getBody()->write(json_encode(["status" => "Success", "data" => ["message" => "Author authenticated successfully", "token" => $newJwt]]));
        } else {
            $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Author authentication failed"]]));
        }
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Invalid or expired token"]]));
    }

    $conn = null;
    return $response;
});

// author postBook
$app->post('/author/postBook', function (Request $request, Response $response, array $args) use ($servername, $username, $password, $dbname) {
    $data = json_decode($request->getBody());

    // Check if the token is provided
    if (!isset($data->token)) {
        return $response->withStatus(400)->write(json_encode(["status" => "Failed", "data" => ["title" => "Token is required"]]));
    }

    $jwt = $data->token;

    // Check if the provided token matches the one stored in the session
    if (!isset($_SESSION['auth_token']) || $_SESSION['auth_token'] !== $jwt) {
        return $response->withStatus(403)->write(json_encode(["status" => "Failed", "data" => ["title" => "Invalid token or token expired"]]));
    }

    // Validate and consume the token
    try {
        $decoded = JWT::decode($jwt, new Key('thisiskey', 'HS256'));
        $usernameFromToken = $decoded->data->name; // Use the decoded username

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch the author's authorid using the username from the token
        $stmt = $conn->prepare("SELECT authorid FROM authors WHERE username = :uname");
        $stmt->execute([':uname' => $usernameFromToken]);
        $author = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$author) {
            return $response->withStatus(404)->write(json_encode(["status" => "Failed", "data" => ["title" => "Author not found"]]));
        }

        $authorid = $author['authorid'];
        $bookTitle = $data->title;

        // Validate book title
        if (empty($bookTitle)) {
            return $response->withStatus(400)->write(json_encode(["status" => "Failed", "data" => ["title" => "Book title is required"]]));
        }

        // Insert the book into the database
        $sql = "INSERT INTO books (title, authorid) VALUES (:title, :authorid)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $bookTitle,
            ':authorid' => $authorid
        ]);

        $response->getBody()->write(json_encode(["status" => "Success", "data" => ["message" => "Book posted successfully"]]));

    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Token invalid or expired"]]));
    }

    return $response;
});


// author viewBook
$app->post('/books/viewBooks', function (Request $request, Response $response, array $args) use ($servername, $username, $password, $dbname) {
    // Check if the session token is available
    if (!isset($_SESSION['auth_token'])) {
        return $response->withStatus(403)->write(json_encode(["status" => "Failed", "data" => ["title" => "Access denied"]]));
    }

    $jwt = $_SESSION['auth_token'];

    try {
        // Decode the token to extract the username
        $decoded = JWT::decode($jwt, new Key('thisiskey', 'HS256'));
        $usernameFromToken = $decoded->data->name;

        // Connect to the database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch the author ID using the username from the token
        $stmt = $conn->prepare("SELECT authorid FROM authors WHERE username = :uname");
        $stmt->execute([':uname' => $usernameFromToken]);
        $author = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$author) {
            return $response->withStatus(404)->write(json_encode(["status" => "Failed", "data" => ["title" => "Author not found"]]));
        }

        $authorid = $author['authorid'];

        // Retrieve the books posted by the author
        $stmt = $conn->prepare("SELECT bookid, title FROM books WHERE authorid = :authorid");
        $stmt->execute([':authorid' => $authorid]);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($books) {
            $response->getBody()->write(json_encode(["status" => "Success", "data" => $books]));
        } else {
            $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "No books found for this author"]]));
        }

    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Token invalid or expired"]]));
    }

    return $response;
});

// user-author viewAuthor
$app->post('/viewAuthor', function (Request $request, Response $response, array $args) use ($servername, $username, $password, $dbname) {
    // Check if the session token is available
    if (!isset($_SESSION['auth_token'])) {
        return $response->withStatus(403)->write(json_encode(["status" => "fail", "data" => ["title" => "Access denied, please log in"]]));
    }

    $jwt = $_SESSION['auth_token'];

    try {
        // Decode the token to extract the username
        $decoded = JWT::decode($jwt, new Key('thisiskey', 'HS256'));
        $usernameFromToken = $decoded->data->name;

        // Connect to the database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch all authors from the database
        $stmt = $conn->prepare("SELECT authorid, username FROM authors");
        $stmt->execute();
        $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($authors) {
            $response->getBody()->write(json_encode(["status" => "Success", "data" => $authors]));
        } else {
            $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "No authors found"]]));
        }

    } catch (Exception $e) {
        $response->getBody()->write(json_encode(["status" => "Failed", "data" => ["title" => "Token invalid or expired"]]));
    }

    return $response;
});


// user author viewBookAndAuthor
$app->post('/books-authorView', function (Request $request, Response $response, array $args) use ($servername, $username, $password, $dbname) {
    // Get the raw POST data
    $data = json_decode($request->getBody());

    // Check if the authorid and bookid are provided
    if (!isset($data->authorid) || !isset($data->bookid)) {
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json')->write(json_encode([
            "status" => "Failed",
            "message" => "Missing authorid or bookid"
        ]));
    }

    $author_id = $data->authorid;
    $book_id = $data->bookid;

    // Check if the session token is available
    if (!isset($_SESSION['auth_token'])) {
        return $response->withStatus(403)->withHeader('Content-Type', 'application/json')->write(json_encode([
            "status" => "Failed",
            "message" => "Access denied"
        ]));
    }

    $jwt = $_SESSION['auth_token'];

    try {
        // Decode the token to extract user data (if necessary)
        $decoded = JWT::decode($jwt, new Key('thisiskey', 'HS256'));

        // Connect to the database
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch author information
        $query_author = "SELECT * FROM authors WHERE authorid = :authorid";
        $stmt_author = $pdo->prepare($query_author);
        $stmt_author->bindParam(':authorid', $author_id);
        $stmt_author->execute();
        $author = $stmt_author->fetch(PDO::FETCH_ASSOC);

        // Fetch book information
        $query_book = "SELECT * FROM books WHERE bookid = :bookid";
        $stmt_book = $pdo->prepare($query_book);
        $stmt_book->bindParam(':bookid', $book_id);
        $stmt_book->execute();
        $book = $stmt_book->fetch(PDO::FETCH_ASSOC);

        // Check if both author and book are found
        if ($author && $book) {
            $response_data = [
                "status" => "Success",
                "data" => [
                    "author" => [
                        "id" => $author['authorid'],
                        "username" => $author['username']
                    ],
                    "book" => [
                        "id" => $book['bookid'],
                        "title" => $book['title']
                    ]
                ]
            ];
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json')->write(json_encode($response_data));
        } else {
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json')->write(json_encode([
                "status" => "Failed",
                "message" => "Author or Book not found"
            ]));
        }
    } catch (Exception $e) {
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json')->write(json_encode([
            "status" => "Failed",
            "message" => "Connection failed: " . $e->getMessage()
        ]));
    }
});

$app->run();
?>
