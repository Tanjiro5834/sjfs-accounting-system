<?php
interface EventListenerInterface {
    public function handle(string $event, array $payload): void;
}