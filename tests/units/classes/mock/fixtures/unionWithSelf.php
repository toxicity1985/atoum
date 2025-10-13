<?php

namespace atoum\atoum\tests\units\mock\fixtures;

class unionWithSelf
{
    public function returnSelfOrString(): self|string
    {
        return $this;
    }

    public function returnStaticOrInt(): static|int
    {
        return $this;
    }
}

class unionWithParentBase
{
    public function baseMethod(): self
    {
        return $this;
    }
}

class unionWithParent extends unionWithParentBase
{
    public function returnParentOrNull(): parent|null
    {
        return $this;
    }
}
