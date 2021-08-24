<?php

use function PHPSTORM_META\map;

class User
{
    public static function auth()
    {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $user = DB::table('users')->where('id', $user_id)->getOne();
            return $user;
        } else {
            return false;
        }
    }
    public function validateInput($request, $reqType = 'login')
    {
        $error = [];
        if (isset($request)) {
            // Validate Name in Register
            if ($reqType !== 'login' && empty($request['name'])) {
                $error[] = "Name field is required";
            }

            // Validate Email in Register or Login
            if (empty($request['email'])) {
                $error[] = "Email field is required";
            }
            if (!empty($request['email']) && !filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
                $error[] = "Email must be valid";
            }

            // Validate Password for login/register
            if ($reqType !== 'update' && empty($request['password'])) {
                $error[] = "Password field is required";
            }
            if (!empty($request['password']) && strlen($request['password']) < 3) {
                $error[] = "Password must be longer than 3 characters";
            }
            return $error;
        }
    }

    public function emailExists($email, $exclude = "")
    {
        $user = DB::table('users')->where('email', $email)->getOne();
        if ($user && $user->email !== $exclude) {
            return true; // Real user out there with the same email
        } elseif ($user && $user->email === $exclude) {
            return false; // Your same email
        } else {
            return false; // No users with this email yet
        }
    }
    public function register($request)
    {
        $error = $this->validateInput($request, $reqType = 'register');
        if ($this->emailExists($request['email'])) {
            $error[] = "Email already exists";
        }
        if (count($error)) {
            return $error;
        } else {
            // Insert User
            $createdUser = DB::create('users', [
                "name" => Helper::filter($request['name']),
                "email" => Helper::filter($request['email']),
                "slug" => Helper::makeSlug($request['name']),
                "password" => password_hash($request['password'], PASSWORD_BCRYPT)
            ]);

            // Insert Session
            $_SESSION['user_id'] = $createdUser->id;
            return true;
        }
    }

    public function login($request)
    {
        $error = $this->validateInput($request, $reqType = 'login');
        $email = Helper::filter($request['email']);
        $password = $request['password'];

        // Check Email
        $user = DB::table('users')->where('email', $email)->getOne();
        if (!$user) {
            $error[] = "Wrong Email";
            return $error;
        }

        // Check Password
        $hashed_password = $user->password;
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user->id;
            return true;
        } else {
            $error[] = "Wrong Password";
            return $error;
        }
    }

    public function update($request, $files)
    {
        // Validate Raw Input
        $error = $this->validateInput($request, $reqType = "update");

        // Check if slug exists
        $slug = $request['user_slug'];
        $user = DB::table('users')->where('slug', $slug)->getOne();
        // If users do not match
        if (!$user || $user->id !== $_SESSION['user_id']) {
            $error[] = "Unknown User";
        }
        // Unique Email Checks
        if ($user && $this->emailExists($request['email'], $user->email)) {
            $error[] = "Email Already Exists";
        }
        // Return from function if errors exists
        if (count($error)) {
            return $error;
        }

        $password = $request['password'] ? password_hash($request['password'], PASSWORD_BCRYPT) : $user->password;
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) { // Upload only when image is fully uploaded from client side
            $image = $files['image'];
            if (!Helper::checkAllowedType($image['name'])) {
                $error[] = "File type not allowed";
                return $error;
            }
            $targetPath = 'assets/user/' . mt_rand() . $image['name'];
            $tmpPath = $image['tmp_name'];
            $uploadStatus = move_uploaded_file($tmpPath, $targetPath);
            if (!$uploadStatus) {
                $error[] = "File upload error";
                return $error;
            }
            Helper::removeFile($user->image);
        } else {
            $targetPath = $user->image;
        }

        $updatedUser = DB::update('users', [
            "name" => Helper::filter($request['name']),
            "email" => Helper::filter($request['email']),
            "image" => $targetPath,
            "password" => $password
        ], $user->id);

        return $updatedUser ? true : ["Something wrong"];
    }
}
