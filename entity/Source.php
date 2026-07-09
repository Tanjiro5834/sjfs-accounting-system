<?php
require_once __DIR__ . '/../utils/AutoModel.php';
class Source {
    use AutoModel;

    private ?int $id = null;
    private int $campus_id;
    private int $collection_type_id;
    private int $bank_account_id;
    private ?string $source_type = null;
    private float $amount;
    private string $transaction_date;
    private ?string $remarks = null;
    private int $created_by;
    private ?string $created_at = null;
}