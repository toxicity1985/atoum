<?php

namespace atoum\atoum\tests\units\mock\fixtures;

interface interfaceWithSelf
{
    public function returnSelf(): self;

    public function returnStatic(): static;
}
