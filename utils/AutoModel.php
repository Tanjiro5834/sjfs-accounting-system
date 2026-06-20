<?php
trait AutoModel {
    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function __get(string $name): mixed {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \Exception("Property {$name} does not exist.");
    }

    public function __set(string $name, mixed $value): void {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            throw new \Exception("Property {$name} does not exist.");
        }
    }

    public function toArray(): array {
        return get_object_vars($this);
    }
}