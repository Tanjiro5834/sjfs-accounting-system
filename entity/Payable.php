<?php
require_once __DIR__ . '/../utils/AutoModel.php';
class Payable {
    use AutoModel;

    private ?int $id = null;
    private string $payee;
    private ?string $check_number = null;
    private int $bank_account_id;
    private float $amount;
    private string $transaction_date;
    private ?string $remarks = null;
    private int $created_by;
    private ?string $created_at = null;
}