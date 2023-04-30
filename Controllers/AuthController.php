<?php

class AuthController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function register($data)
    {
        // Validate input data
        $name = $data['name'];
        $email = $data['email'];
        $password = $data['password'];
        $confirmPassword = $data['confirm_password'];

        // Validate name
        if (!preg_match("/^[a-zA-Z '-]{2,50}$/", $name)) {
            apiResponse(400, "Invalid name");
        }
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            apiResponse(400, "Invalid email address.");
        }
        // Validate password
        if (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+-=])(?=.*[a-zA-Z]).{8,}$/", $password)) {
            apiResponse(400, "Invalid password");
        }
        // Validate password
        if ($password !== $confirmPassword) {
            apiResponse(400, "password does not match with confirm password");
        }

        // Check if email already exists
        $user = $this->db->select("users", array("email" => $email), 1);
        if ($user) {
            apiResponse(400, "Email already exists.");
        }

        // Create new user
        $password = md5($password);
        $user_id = $this->db->insert("users", array("name" => $name, "password" => $password, "email" => $email));
        if ($user_id > 0) {
            apiResponse(200, 'User created successfully');
        } else {
            apiResponse(400, 'Error: User not created');
        }
    }

    public function login($data)
    {
        $email = $data['email'];
        $password = $data['password'];
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            apiResponse(400, "Invalid email address.");
        }
        // Validate password
        if ($password == '') {
            apiResponse(400, "Please enter password.");
        }

        // Check if name exists
        $user = $this->db->select('users', array("email" => $email), 1);
        if (!$user) {
            apiResponse(400, "Email address does not exist.");
        }
        // Verify password
        if (md5($password) != $user["password"]) {
            apiResponse(400, "Password does not match.");
        }

        // Create JWT token
        $payload = array(
            "id" => $user["id"],
            "name" => $user["name"],
            "email" => $user["email"]
        );
        $jwt = new JwtHandler();

        $jwt = $jwt->jwtEncodeData(
            BASE_URL,
            $payload
        );
        $user['token'] = $jwt;
        apiResponse(200, "Login successfully.", $user);
    }
}
