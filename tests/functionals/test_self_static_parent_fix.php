<?php

/**
 * Test fonctionnel pour vérifier que self, static et parent sont correctement gérés
 * dans les types de retour des mocks.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

// Test 1: Union types avec self
class TestUnionWithSelf
{
    public function returnSelfOrString(): self|string
    {
        return $this;
    }
}

// Test 2: Union types avec static
class TestUnionWithStatic
{
    public function returnStaticOrInt(): static|int
    {
        return $this;
    }
}

// Test 3: Union types avec parent
class TestParentBase
{
    public function baseMethod(): self
    {
        return $this;
    }
}

class TestUnionWithParent extends TestParentBase
{
    public function returnParentOrNull(): parent|null
    {
        return $this;
    }
}

// Test 4: Abstract avec static
abstract class TestAbstractWithStatic
{
    abstract public function returnStatic(): static;
}

$generator = new \atoum\atoum\mock\generator();

echo "Test 1: Union avec self..." . PHP_EOL;
$generator->generate(TestUnionWithSelf::class);
$mock1 = new \mock\TestUnionWithSelf();
$mock1->getMockController()->returnSelfOrString = function () use ($mock1) {
    return $mock1;
};
$result = $mock1->returnSelfOrString();
assert($result === $mock1, 'Test 1 failed: returnSelfOrString should return the mock instance');
echo "✓ Test 1 passed" . PHP_EOL;

echo "Test 2: Union avec static..." . PHP_EOL;
$generator->generate(TestUnionWithStatic::class);
$mock2 = new \mock\TestUnionWithStatic();
$mock2->getMockController()->returnStaticOrInt = function () use ($mock2) {
    return $mock2;
};
$result = $mock2->returnStaticOrInt();
assert($result === $mock2, 'Test 2 failed: returnStaticOrInt should return the mock instance');
echo "✓ Test 2 passed" . PHP_EOL;

echo "Test 3: Union avec parent..." . PHP_EOL;
$generator->generate(TestUnionWithParent::class);
$mock3 = new \mock\TestUnionWithParent();
$mock3->getMockController()->returnParentOrNull = function () use ($mock3) {
    return $mock3;
};
$result = $mock3->returnParentOrNull();
assert($result === $mock3, 'Test 3 failed: returnParentOrNull should return the mock instance');
echo "✓ Test 3 passed" . PHP_EOL;

echo "Test 4: Abstract avec static (valeur par défaut)..." . PHP_EOL;
$generator->generate(TestAbstractWithStatic::class);
$mock4 = new \mock\TestAbstractWithStatic();
// Ne pas définir de mock controller, utiliser la valeur par défaut
$result = $mock4->returnStatic();
assert($result === $mock4, 'Test 4 failed: returnStatic should return $this by default');
echo "✓ Test 4 passed" . PHP_EOL;

echo PHP_EOL . "=== TOUS LES TESTS SONT PASSÉS ===" . PHP_EOL;
