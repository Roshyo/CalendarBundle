<?php

namespace Roshyo\PlanningBundle\Validator;

use BaseBundle\Utils\TranslatableTrait;
use EmployeeBundle\Entity\Employee;
use Roshyo\PlanningBundle\Calendar\Items\Item;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/** @Annotation */
class ItemValidator extends ConstraintValidator implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use TranslatableTrait;

    /**
     * @param Item       $object
     * @param Constraint $constraint
     *
     * @internal param ExecutionContextInterface $context
     * @internal param $payload
     */
    public function validate($object, Constraint $constraint)
    {
        /** @var Employee $employee */
        $employee = $object->getResource();
        $handler = $this->container->get('employee.employee_handler');
        $handler->setEmployee($employee);

        if (!$handler->canHandleConflict($object))
            $this->context->addViolation($this->trans('front.item.error.conflict'));
    }
}