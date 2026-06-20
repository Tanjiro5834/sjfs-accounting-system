<?php
interface ReportStrategyInterface {
    public function generate(string $dateFrom, string $dateTo, array $options = []): array;
}