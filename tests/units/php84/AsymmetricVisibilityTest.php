<?php

namespace atoum\atoum\tests\units\php84;

use atoum\atoum;
use atoum\atoum\tests\units\php84\fixtures\BankAccount;
use atoum\atoum\tests\units\php84\fixtures\Counter;
use atoum\atoum\tests\units\php84\fixtures\User;

/**
 * Tests demonstrating asymmetric visibility with atoum
 * 
 * @requires PHP >= 8.4
 */
class AsymmetricVisibilityTest extends atoum
{
    /**
     * Test BankAccount with asymmetric visibility
     */
    public function testBankAccountBalanceIsReadOnly()
    {
        $this
            ->given($account = new BankAccount('ACC123'))
            ->then
                // Can read balance
                ->float($account->balance)->isEqualTo(0.0)
                ->string($account->accountId)->isEqualTo('ACC123')
                
                // Cannot write directly (PHP 8.4 enforces this)
                ->exception(function() use ($account) {
                    $account->balance = 1000.0;
                })
                    ->isInstanceOf(\Error::class)
                    ->message->contains('Cannot modify')
        ;
    }

    public function testBankAccountDeposit()
    {
        $this
            ->given($account = new BankAccount('ACC456'))
            ->when($account->deposit(100.0))
            ->then
                ->float($account->balance)->isEqualTo(100.0)
                ->integer($account->getTransactionCount())->isEqualTo(1)
            
            ->when($account->deposit(50.0))
            ->then
                ->float($account->balance)->isEqualTo(150.0)
                ->integer($account->getTransactionCount())->isEqualTo(2)
        ;
    }

    public function testBankAccountWithdrawal()
    {
        $this
            ->given($account = new BankAccount('ACC789'))
            ->and($account->deposit(100.0))
            ->when($account->withdraw(30.0))
            ->then
                ->float($account->balance)->isEqualTo(70.0)
                ->integer($account->getTransactionCount())->isEqualTo(2)
        ;
    }

    public function testBankAccountInsufficientFunds()
    {
        $this
            ->given($account = new BankAccount('ACC999'))
            ->and($account->deposit(50.0))
            ->exception(function() use ($account) {
                $account->withdraw(100.0);
            })
                ->isInstanceOf(\ValueError::class)
                ->hasMessage('Insufficient funds')
        ;
    }

    public function testBankAccountTransactionsAreReadOnly()
    {
        $this
            ->given($account = new BankAccount('ACC001'))
            ->and($account->deposit(100.0))
            ->then
                // Can read transactions
                ->array($account->transactions)
                    ->hasSize(1)
                    ->child[0](function($transaction) {
                        $this->string($transaction['type'])->isEqualTo('deposit');
                        $this->float($transaction['amount'])->isEqualTo(100.0);
                    })
                
                // Cannot modify transactions directly
                ->exception(function() use ($account) {
                    $account->transactions = [];
                })
                    ->isInstanceOf(\Error::class)
        ;
    }

    /**
     * Test Counter with increment-only value
     */
    public function testCounterValueIsReadOnly()
    {
        $this
            ->given($counter = new Counter())
            ->then
                ->integer($counter->value)->isEqualTo(0)
                ->integer($counter->maxValue)->isEqualTo(0)
                
                // Cannot set value directly
                ->exception(function() use ($counter) {
                    $counter->value = 10;
                })
                    ->isInstanceOf(\Error::class)
        ;
    }

    public function testCounterIncrement()
    {
        $this
            ->given($counter = new Counter())
            ->when($counter->increment(5))
            ->then
                ->integer($counter->value)->isEqualTo(5)
                ->integer($counter->maxValue)->isEqualTo(5)
            
            ->when($counter->increment(3))
            ->then
                ->integer($counter->value)->isEqualTo(8)
                ->integer($counter->maxValue)->isEqualTo(8)
            
            // Reset to 0 but maxValue stays
            ->when($counter->reset())
            ->then
                ->integer($counter->value)->isEqualTo(0)
                ->integer($counter->maxValue)->isEqualTo(8)
        ;
    }

    /**
     * Test mocking classes with asymmetric visibility
     */
    public function testMockBankAccount()
    {
        // Skip if PHP < 8.4
        if (version_compare(PHP_VERSION, '8.4.0', '<')) {
            $this->skip('Asymmetric visibility requires PHP 8.4+');
        }

        $this
            ->given($mock = new \mock\examples\php84\BankAccount('MOCK123'))
            ->then
                // Mock maintains asymmetric visibility
                ->float($mock->balance)->isEqualTo(0.0)
                ->string($mock->accountId)->isEqualTo('MOCK123')
                
                // Can mock methods
                ->when($this->calling($mock)->deposit = function($amount) {
                    $this->balance = $amount * 2; // Double the deposit
                })
                ->and($mock->deposit(50))
                ->then
                    ->mock($mock)
                        ->call('deposit')->withArguments(50)->once()
        ;
    }

    public function testMockCounterWithAsymmetricVisibility()
    {
        if (version_compare(PHP_VERSION, '8.4.0', '<')) {
            $this->skip('Asymmetric visibility requires PHP 8.4+');
        }

        $this
            ->given($mock = new \mock\examples\php84\Counter())
            ->and($this->calling($mock)->increment = null)
            ->when($mock->increment(10))
            ->then
                ->mock($mock)
                    ->call('increment')->withArguments(10)->once()
                
                // Properties still respect asymmetric visibility
                ->integer($mock->value)->isEqualTo(0) // Not modified (mocked)
                
                // Cannot set directly even on mock
                ->exception(function() use ($mock) {
                    $mock->value = 100;
                })
                    ->isInstanceOf(\Error::class)
        ;
    }

    /**
     * Test User with protected asymmetric visibility
     */
    public function testUserWithProtectedAsymmetricVisibility()
    {
        $this
            ->given($user = new User(1, 'John Doe', 'john@example.com'))
            ->then
                ->integer($user->id)->isEqualTo(1)
                ->string($user->name)->isEqualTo('John Doe')
                ->object($user->createdAt)->isInstanceOf(\DateTimeImmutable::class)
                
                // ID is read-only
                ->exception(function() use ($user) {
                    $user->id = 999;
                })
                    ->isInstanceOf(\Error::class)
                
                // createdAt is read-only
                ->exception(function() use ($user) {
                    $user->createdAt = new \DateTimeImmutable();
                })
                    ->isInstanceOf(\Error::class)
        ;
    }

    public function testUserEmailUpdate()
    {
        $this
            ->given($user = new User(1, 'Jane', 'jane@example.com'))
            ->when($user->updateEmail('jane.new@example.com'))
            ->then
                // Email updated successfully
                ->variable($user->getEmail())->isNull() // Protected method not accessible
            
            ->exception(function() use ($user) {
                $user->updateEmail('invalid-email');
            })
                ->isInstanceOf(\ValueError::class)
                ->hasMessage('Invalid email address')
        ;
    }
}

