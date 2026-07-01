<?php
class PayableResponse {
    public int $id;
    public string $payee;
    public ?string $check_number;
    public int $bank_account_id;
    public string $bank_name;
    public float $amount;
    public string $transaction_date;
    public ?string $remarks;
    public string $created_by_name;
    public string $created_at;

    public function __construct(array $row) {
        $this->id               = (int) $row['id'];
        $this->payee            = $row['payee'];
        $this->check_number     = $row['check_number'] ?? null;
        $this->bank_account_id  = (int) $row['bank_account_id']; 
        $this->bank_name        = $row['bank_name'];
        $this->amount           = (float) $row['amount'];
        $this->transaction_date = $row['transaction_date'];
        $this->remarks          = $row['remarks'] ?? null;
        $this->created_by_name  = $row['created_by_name'];
        $this->created_at       = $row['created_at'];
    }

    public static function fromArray(array $rows): array {
        return array_map(fn($row) => new self($row), $rows);
    }
}