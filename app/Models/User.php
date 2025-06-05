<?php

namespace app\Models;

class User extends ActiveRecordEntity
{
    protected $user_id;
    public $username;
    public $email;
    protected $password_hash;
    public $role;
    public $created_at;

    protected static function getTableName(): string
    {
        return 'Users';
    }

    public function getId(): int
    {
        return $this->user_id;
    }

    public static function createUser(string $username, string $email, string $password): self
    {
        $user = new self();
        $user->username = $username;
        $user->email = $email;
        $user->password_hash = password_hash($password, PASSWORD_BCRYPT);
        $user->role = 'author';
        $user->save();
        return $user;
    }

    public static function findByEmail(string $email): ?self
    {
        return self::findOneBy('email', $email);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
