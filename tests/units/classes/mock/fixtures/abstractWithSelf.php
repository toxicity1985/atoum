<?php

namespace atoum\atoum\tests\units\mock\fixtures;

abstract class abstractWithSelf
{
    abstract public function returnSelf(): self;

    abstract public function returnStatic(): static;
}

abstract class abstractWithSelfChild extends abstractWithSelf
{
    // Hérite des méthodes abstraites
}
