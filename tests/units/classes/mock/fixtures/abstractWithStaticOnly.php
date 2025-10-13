<?php

namespace atoum\atoum\tests\units\mock\fixtures;

abstract class abstractWithStaticOnly
{
    abstract public function returnStatic(): static;
}
