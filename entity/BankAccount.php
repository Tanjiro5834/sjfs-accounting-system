<?php
require_once __DIR__ . '/../utils/AutoModel.php';

class BankAccount {
    use AutoModel;

    private ?int $id = null;
    private string $account_name;
    private string $bank_name;
    private ?string $account_number = null;
    private float $opening_balance = 0.00;
    private ?int $campus_id = null;
    private int $is_active = 1;
    private ?string $created_at = null;
}