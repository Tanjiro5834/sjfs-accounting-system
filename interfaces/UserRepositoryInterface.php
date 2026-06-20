<?php
interface UserRepositoryInterface {
    public function findById(int $id): ?array;
    public function findByEmail(string $email): ?array;
    public function findAll(): array;
    public function save(User $user): int;
    public function update(int $id, User $user): bool;
    public function deactivate(int $id): bool;
}