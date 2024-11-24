// Create a new XMLHttpRequest object
let xhr = new XMLHttpRequest();

// Configure it: GET-request for the URL /getUserData
xhr.open('GET', '/getUserData', true);

// Set up the function to handle the response
xhr.onreadystatechange = function() {
    if (xhr.readyState === XMLHttpRequest.DONE) { // Request completed
        if (xhr.status === 200) { // HTTP status 200 means the request was successful
            try {
                // Parse the JSON response
                let userData = JSON.parse(xhr.responseText);
                
                // Access and display data from the parsed object
                console.log("User Name:", userData.name);  // Example: Output User Name
                console.log("User Age:", userData.age);    // Example: Output User Age
                console.log("User City:", userData.city);  // Example: Output User City

            } catch (error) {
                console.error("Error parsing JSON:", error);
            }
        } else {
            console.error("Error in request:", xhr.status);
        }
    }
};

// Send the request
xhr.send();
