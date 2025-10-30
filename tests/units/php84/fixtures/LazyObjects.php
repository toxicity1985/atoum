<?php

namespace atoum\atoum\tests\units\php84\fixtures;

/**
 * Examples of PHP 8.4 Lazy Objects
 *
 * Lazy Objects allow deferring object initialization until first access,
 * improving performance by avoiding expensive operations until needed.
 *
 * @requires PHP >= 8.4
 */

/**
 * Example of an expensive-to-create service
 */
class ExpensiveService
{
    private array $data = [];
    private bool $initialized = false;

    public function __construct()
    {
        // Simulate expensive initialization
        $this->loadData();
        $this->initialized = true;
    }

    private function loadData(): void
    {
        // Simulate database query, file loading, etc.
        $this->data = [
            'config' => 'loaded',
            'cache' => 'warmed',
            'connections' => 'established'
        ];
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function process(string $input): string
    {
        return strtoupper($input) . ' - processed';
    }
}

/**
 * Example of a repository with lazy loading
 */
class UserRepository
{
    private ?array $users = null;

    public function __construct(private string $dataSource)
    {
        // Don't load users yet
    }

    public function getUsers(): array
    {
        if ($this->users === null) {
            $this->loadUsers();
        }
        return $this->users;
    }

    private function loadUsers(): void
    {
        // Simulate expensive database query
        $this->users = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ];
    }

    public function findById(int $id): ?array
    {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null;
    }
}

/**
 * Helper class to demonstrate lazy object creation
 */
class LazyObjectFactory
{
    /**
     * Create a lazy ghost of ExpensiveService
     * The object exists but __construct() is not called until first property/method access
     */
    public static function createLazyGhost(): ExpensiveService
    {
        $reflector = new \ReflectionClass(ExpensiveService::class);

        return $reflector->newLazyGhost(function (ExpensiveService $ghost) {
            // This initializer is called on first access
            $ghost->__construct();
        });
    }

    /**
     * Create a lazy proxy for ExpensiveService
     * A proxy object that creates the real object on first access
     */
    public static function createLazyProxy(): ExpensiveService
    {
        $reflector = new \ReflectionClass(ExpensiveService::class);

        return $reflector->newLazyProxy(function () {
            // This factory is called on first access
            return new ExpensiveService();
        });
    }

    /**
     * Check if an object is an uninitialized lazy object
     */
    public static function isLazy(object $object): bool
    {
        $reflector = new \ReflectionClass($object);
        return $reflector->isUninitializedLazyObject($object);
    }

    /**
     * Force initialization of a lazy object
     */
    public static function initialize(object $object): void
    {
        $reflector = new \ReflectionClass($object);
        if ($reflector->isUninitializedLazyObject($object)) {
            $reflector->initializeLazyObject($object);
        }
    }
}

/**
 * Example: Service container with lazy services
 */
class ServiceContainer
{
    private array $services = [];
    private array $factories = [];

    public function register(string $name, callable $factory): void
    {
        $this->factories[$name] = $factory;
    }

    public function get(string $name): object
    {
        if (!isset($this->services[$name])) {
            if (!isset($this->factories[$name])) {
                throw new \RuntimeException("Service '$name' not found");
            }

            // Create lazy proxy
            $factory = $this->factories[$name];
            $reflector = new \ReflectionClass($factory());

            $this->services[$name] = $reflector->newLazyProxy($factory);
        }

        return $this->services[$name];
    }

    public function isServiceInitialized(string $name): bool
    {
        if (!isset($this->services[$name])) {
            return false;
        }

        $reflector = new \ReflectionClass($this->services[$name]);
        return !$reflector->isUninitializedLazyObject($this->services[$name]);
    }
}

/**
 * Example: Configuration loader with lazy properties
 */
class Configuration
{
    private ?array $config = null;

    public function __construct(private string $configFile)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->config === null) {
            $this->loadConfig();
        }

        return $this->config[$key] ?? $default;
    }

    private function loadConfig(): void
    {
        // Simulate expensive config file parsing
        $this->config = [
            'app_name' => 'My App',
            'debug' => true,
            'database' => [
                'host' => 'localhost',
                'port' => 3306,
            ]
        ];
    }

    /**
     * Create a lazy instance of Configuration
     */
    public static function createLazy(string $configFile): self
    {
        $reflector = new \ReflectionClass(self::class);

        return $reflector->newLazyGhost(function (self $ghost) use ($configFile) {
            $ghost->__construct($configFile);
        });
    }
}
