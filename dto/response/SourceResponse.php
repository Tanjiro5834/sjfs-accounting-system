<?php
class SourceResponse {
    public int $id;
    public string $campus_name;
    public string $type_code;
    public string $type_name;
    public string $bank_name;
    public float $amount;
    public string $transaction_date;
    public ?string $remarks;
    public string $created_by_name;
    public string $created_at;

    public function __construct(array $row) {
        $this->id               = (int) $row['id'];
        $this->campus_name      = $row['campus_name'];
        $this->type_code        = $row['type_code'];
        $this->type_name        = $row['type_name'];
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