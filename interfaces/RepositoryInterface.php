<?php
interface RepositoryInterface {
    public function findAll(): array;
    public function delete(int $id): bool;
}