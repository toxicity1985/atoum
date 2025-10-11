<?php

namespace atoum\atoum\tests\units\php84\fixtures;

/**
 * Examples of PHP 8.4 Array Functions
 * 
 * PHP 8.4 introduces new array functions that make it easier
 * to work with arrays without having to write loops.
 * 
 * @requires PHP >= 8.4
 */
class ArrayFunctions
{
    /**
     * Example using array_find() to find the first matching element
     */
    public function findFirstUser(array $users, string $role): ?array
    {
        // Before PHP 8.4: loop + break
        // foreach ($users as $user) {
        //     if ($user['role'] === $role) {
        //         return $user;
        //     }
        // }
        // return null;
        
        // PHP 8.4+: array_find()
        return array_find($users, fn($user) => $user['role'] === $role);
    }
    
    /**
     * Example using array_find_key() to find the key of first match
     */
    public function findUserIndex(array $users, int $userId): int|string|null
    {
        return array_find_key($users, fn($user) => $user['id'] === $userId);
    }
    
    /**
     * Example using array_any() to check if at least one element matches
     */
    public function hasAdminUser(array $users): bool
    {
        // Before PHP 8.4
        // foreach ($users as $user) {
        //     if ($user['role'] === 'admin') {
        //         return true;
        //     }
        // }
        // return false;
        
        // PHP 8.4+
        return array_any($users, fn($user) => $user['role'] === 'admin');
    }
    
    /**
     * Example using array_all() to check if all elements match
     */
    public function allUsersActive(array $users): bool
    {
        // Before PHP 8.4
        // foreach ($users as $user) {
        //     if (!$user['active']) {
        //         return false;
        //     }
        // }
        // return true;
        
        // PHP 8.4+
        return array_all($users, fn($user) => $user['active']);
    }
    
    /**
     * Combined example: validation with array functions
     */
    public function validateForm(array $fields): array
    {
        $errors = [];
        
        // Check if any field is empty
        if (array_any($fields, fn($value) => empty($value))) {
            $errors[] = 'Some fields are empty';
        }
        
        // Find first invalid email
        $invalidEmail = array_find($fields, function($value, $key) {
            return str_ends_with($key, 'email') 
                && !filter_var($value, FILTER_VALIDATE_EMAIL);
        });
        
        if ($invalidEmail !== null) {
            $errors[] = 'Invalid email found: ' . $invalidEmail;
        }
        
        return $errors;
    }
    
    /**
     * Example with complex objects
     */
    public function getFirstExpiredItem(array $items): ?object
    {
        return array_find($items, function($item) {
            return $item->expiresAt < new \DateTime();
        });
    }
    
    /**
     * Performance comparison helper (for documentation)
     */
    public function performanceExample(array $largeArray): mixed
    {
        // This is much more readable and potentially optimized by PHP internals
        return array_find($largeArray, fn($item) => $item > 1000);
        
        // Instead of:
        // foreach ($largeArray as $item) {
        //     if ($item > 1000) {
        //         return $item;
        //     }
        // }
    }
}

