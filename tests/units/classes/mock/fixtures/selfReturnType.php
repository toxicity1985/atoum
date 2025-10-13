<?php

namespace atoum\atoum\tests\units\mock\fixtures;

class selfReturnType
{
    public function returnSelf(): self
    {
        return $this;
    }

    public function returnNullableSelf(): ?self
    {
        return $this;
    }
}
