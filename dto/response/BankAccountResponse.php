<?php
class BankAccountResponse {
    public int $id;
    public string $account_name;
    public string $bank_name;
    public ?string $account_number;
    public float $opening_balance;
    public ?int $campus_id;
    public int $is_active;
    public float $total_sources;
    public float $total_payables;
    public float $ending_balance;

    public function __construct(array $row) {
        if (empty($row)) throw new InvalidArgumentException("Row data cannot be empty");

        $this->id              = (int)   ($row['id']              ?? throw new InvalidArgumentException("Missing field: id"));
        $this->account_name    = (string) ($row['account_name']   ?? throw new InvalidArgumentException("Missing field: account_name"));
        $this->bank_name       = (string) ($row['bank_name']      ?? throw new InvalidArgumentException("Missing field: bank_name"));
        $this->account_number  = $row['account_number']  ?? null;
        $this->opening_balance = (float)  ($row['opening_balance'] ?? 0.00);
        $this->campus_id       = isset($row['campus_id']) ? (int) $row['campus_id'] : null;
        $this->is_active       = (int)   ($row['is_active']       ?? 1);
        $this->total_sources   = (float)  ($row['total_sources']   ?? 0.00);
        $this->total_payables  = (float)  ($row['total_payables']  ?? 0.00);
        $this->ending_balance  = (float)  ($row['ending_balance']  ?? $this->opening_balance);
    }

    public static function fromArray(array $rows): array {
        if (empty($rows)) return [];
        return array_map(fn($row) => new self($row), $rows);
    }

    public function isActive(): bool {
        return $this->is_active === 1;
    }

    public function hasPositiveBalance(): bool {
        return $this->ending_balance >= 0;
    }

    public function getNetMovement(): float {
        return $this->total_sources - $this->total_payables;
    }

    public function toArray(): array {
        return [
            'id'              => $this->id,
            'account_name'    => $this->account_name,
            'bank_name'       => $this->bank_name,
            'account_number'  => $this->account_number,
            'opening_balance' => $this->opening_balance,
            'campus_id'       => $this->campus_id,
            'is_active'       => $this->is_active,
            'total_sources'   => $this->total_sources,
            'total_payables'  => $this->total_payables,
            'ending_balance'  => $this->ending_balance,
            'net_movement'    => $this->getNetMovement(),
            'is_positive'     => $this->hasPositiveBalance(),
        ];
    }
}