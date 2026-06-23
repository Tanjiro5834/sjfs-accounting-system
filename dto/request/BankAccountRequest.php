<?php
class BankAccountRequest {
    public string $account_name;
    public string $bank_name;
    public ?string $account_number = null;
    public float $opening_balance = 0.00;
    public ?int $campus_id = null;

    public function __construct(array $data) {
        $this->account_name    = trim($data['account_name']);
        $this->bank_name       = trim($data['bank_name']);
        $this->account_number  = $data['account_number'] ?? null;
        $this->opening_balance = (float) ($data['opening_balance'] ?? 0.00);
        $this->campus_id = !empty($data['campus_id']) ? (int) $data['campus_id'] : null;
    }

    public function validate(): array {
        $errors = [];
        if (empty($this->account_name)) $errors[] = "Account name is required";
        if (empty($this->bank_name))    $errors[] = "Bank name is required";
        if ($this->opening_balance < 0) $errors[] = "Opening balance cannot be negative";
        return $errors;
    }
}