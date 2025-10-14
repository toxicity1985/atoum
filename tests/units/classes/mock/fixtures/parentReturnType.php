<?php

namespace atoum\atoum\tests\units\mock\fixtures;

class parentReturnTypeBase
{
    public function baseMethod(): self
    {
        return $this;
    }
}

class parentReturnType extends parentReturnTypeBase
{
    public function returnParent(): parent
    {
        return parent::baseMethod();
    }

    public function returnNullableParent(): ?parent
    {
        return parent::baseMethod();
    }
}
