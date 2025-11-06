<?php

declare(strict_types=1);

namespace tests\support;

use yii\base\Component;

final class ArraySession extends Component
{
    private array $data = [];
    private array $flashes = [];
    private bool $active = true;

    public function open(): void
    {
        $this->active = true;
    }

    public function close(): void
    {
        $this->active = false;
    }

    public function destroy(): void
    {
        $this->data = [];
        $this->flashes = [];
    }

    public function getIsActive(): bool
    {
        return $this->active;
    }

    public function getHasSessionId(): bool
    {
        return true;
    }

    public function regenerateID($deleteOldSession = false): void
    {
        // no-op for tests
    }

    public function set($key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function get($key, $defaultValue = null)
    {
        return $this->data[$key] ?? $defaultValue;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function remove($key)
    {
        $value = $this->data[$key] ?? null;
        unset($this->data[$key]);
        return $value;
    }

    public function removeAll(): void
    {
        $this->data = [];
    }

    public function getId(): string
    {
        return 'test-session';
    }

    public function setId($value): void
    {
    }

    public function getName(): string
    {
        return 'TESTSESSID';
    }

    public function setName($value): void
    {
    }

    public function setSavePath($value): void
    {
    }

    public function getSavePath(): string
    {
        return sys_get_temp_dir();
    }

    public function setFlash($key, $value = true, $removeAfterAccess = true): void
    {
        $this->flashes[$key] = [$value, $removeAfterAccess];
    }

    public function addFlash($key, $value = true): void
    {
        $this->flashes[$key] = [$value, true];
    }

    public function getFlash($key, $defaultValue = null, $delete = true)
    {
        if (!isset($this->flashes[$key])) {
            return $defaultValue;
        }

        [$value, $remove] = $this->flashes[$key];
        if ($delete || $remove) {
            unset($this->flashes[$key]);
        }

        return $value;
    }

    public function getAllFlashes($delete = false): array
    {
        $result = [];
        foreach ($this->flashes as $key => [$value]) {
            $result[$key] = $value;
            if ($delete) {
                unset($this->flashes[$key]);
            }
        }
        return $result;
    }

    public function removeFlash($key)
    {
        if (!isset($this->flashes[$key])) {
            return null;
        }

        [$value] = $this->flashes[$key];
        unset($this->flashes[$key]);
        return $value;
    }

    public function hasFlash($key): bool
    {
        return isset($this->flashes[$key]);
    }

    public function setCookieParams($value): void
    {
    }

    public function getCookieParams(): array
    {
        return [];
    }

    public function getTimeout(): int
    {
        return 0;
    }

    public function setTimeout($value): void
    {
    }

    public function getUseCookies(): bool
    {
        return false;
    }

    public function setUseCookies($value): void
    {
    }

    public function getUseTransparentSessionID(): bool
    {
        return false;
    }

    public function setUseTransparentSessionID($value): void
    {
    }

    public function setGcProbability($value): void
    {
    }

    public function getGcProbability(): array
    {
        return [1, 100];
    }
}
