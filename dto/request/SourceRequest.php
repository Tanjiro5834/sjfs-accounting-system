<?php
class SourceRequest {
    public int $campus_id;
    public int $collection_type_id;
    public int $bank_account_id;
    public ?string $source_type = null;
    public float $amount;
    public string $transaction_date;
    public ?string $remarks = null;

    public function __construct(array $data) {
        $this->campus_id           = (int) $data['campus_id'];
        $this->collection_type_id  = (int) $data['collection_type_id'];
        $this->bank_account_id     = (int) $data['bank_account_id'];
        $this->source_type         = $data['source_type'] ?? null;
        $this->amount              = (float) $data['amount'];
        $this->transaction_date    = $data['transaction_date'];
        $this->remarks             = $data['remarks'] ?? null;
    }

    public function validate(): array {
        $errors = [];
        if (empty($this->campus_id))          $errors[] = "Campus is required";
        if (empty($this->collection_type_id)) $errors[] = "Collection type is required";
        if (empty($this->bank_account_id))    $errors[] = "Bank account is required";
        if ($this->amount <= 0)               $errors[] = "Amount must be greater than 0";
        if (empty($this->transaction_date))   $errors[] = "Transaction date is required";
        return $errors;
    }
}