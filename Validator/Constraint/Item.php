<?php

namespace Roshyo\PlanningBundle\Validator\Constraint;

use Roshyo\PlanningBundle\Validator\ItemValidator;
use Symfony\Component\Validator\Constraint;

/** @Annotation */
class Item extends Constraint
{
    public function validatedBy()
    {
        return ItemValidator::class;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}