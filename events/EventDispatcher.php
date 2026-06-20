<?php
require_once 'interfaces/EventListenerInterface.php';

class EventDispatcher {
    private array $listeners = [];

    public function register(string $event, EventListenerInterface $listener): void {
        if (empty($event)) throw new InvalidArgumentException("Event name cannot be empty");
        $this->listeners[$event][] = $listener;
    }

    public function dispatch(string $event, array $payload): void {
        if (empty($event)) throw new InvalidArgumentException("Event name cannot be empty");
        if (!isset($this->listeners[$event])) return;

        foreach ($this->listeners[$event] as $listener) {
            $listener->handle($event, $payload);
        }
    }

    public function hasListeners(string $event): bool {
        return !empty($this->listeners[$event]);
    }
}