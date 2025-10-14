<?php

namespace atoum\atoum\tests\units\mock\fixtures;

class staticReturnType
{
    public function returnStatic(): static
    {
        return $this;
    }

    public function returnNullableStatic(): ?static
    {
        return $this;
    }
}
