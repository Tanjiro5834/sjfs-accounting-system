<?php
class PayableRequest {
    public string $payee;
    public ?string $check_number = null;
    public int $bank_account_id;
    public float $amount;
    public string $transaction_date;
    public ?string $remarks = null;

    public function __construct(array $data) {
        $this->payee            = trim($data['payee']);
        $this->check_number     = $data['check_number'] ?? null;
        $this->bank_account_id  = (int) $data['bank_account_id'];
        $this->amount           = (float) $data['amount'];
        $this->transaction_date = $data['transaction_date'];
        $this->remarks          = $data['remarks'] ?? null;
    }

    public function validate(): array {
        $errors = [];
        if (empty($this->payee)) $errors[] = "Payee is required";
        if (empty($this->bank_account_id)) $errors[] = "Bank account is required";
        if ($this->amount <= 0) $errors[] = "Amount must be greater than 0";
        if (empty($this->transaction_date)) $errors[] = "Transaction date is required";
        return $errors;
    }
}