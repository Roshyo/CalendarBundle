# PlanningBundle
Provides all planning methods for integration in Symfony 3

# Installation
```bash
$ composer require roshyo/planning-bundle
```

# Configuration
```php
<?php
// app/AppKernel.php
  public function registerBundles()
  {
    ...,
    new Roshyo\PlanningBundle\RoshyoPlanningBundle(),
    ...,
  }
```

```yml
# app/config.yml

...
roshyo_planning:
    resources:
        resource_name:
            class: 'YourNamespace\YourClass'
            items:
                - 'method'
                - 'method1.method2'
```

the resource_name can be anything, like employee, customer, doctor, etc...

the class in class section must extend "Roshyo\PlanningBundle\Calendar\Resources"

```php
<?php
// src/AppBundle/Entity/Employee.php
namespace AppBundle\Entity;

use Roshyo\PlanningBundle\Calendar\Resources\Resource;

class Employee extends Resource
{
  ...
```

Then you can define your fields as usual, and you can map with Doctrine by overriding them or in yml, xml...

Items in item section are a bit more tricky. You have to define which methods return items for the resource.
For example, I define :

```yml
# app/config.yml

...
roshyo_planning:
    resources:
        resource_name:
            class: 'AppBundle\Entity\Employee'
            items:
                - 'meetings'
                - 'daysOff.dayOff'
```

Then, there are two different items for my Resource : Employee::getMeetings() returning an array of Items, and Employee::getDaysOff() which returns an array of items with method DayOff::getDayOff(). This second one allows to mark as Item a linked Entity.

The employee must now have at least :

```php
<?php
// src/AppBundle/Entity/Employee.php
namespace AppBundle\Entity;

use Roshyo\PlanningBundle\Calendar\Resources\Resource;

class Employee extends Resource
{
  /**
   * @return \Roshyo\PlanningBundle\Calendar\Items\Item[]
   */
  public function getMeetings(){}
  
  /**
   * @return array|ArrayCollection|EmployeeDayOff[]
   */
  public function getDaysOff(){}
  ...
```

And the Items:

```php
<?php
// src/AppBundle/Entity/Meeting.php
namespace AppBundle\Entity;

use Roshyo\PlanningBundle\Calendar\Items\Item;

class Employee extends Item
{
  ...
```

```php
<?php
// src/AppBundle/Entity/EmployeeDayOff.php
namespace AppBundle\Entity;

class Employee
{
  /**
   * @return DayOff
   */
  public function getDayOff(){}
  ...
```

```php
<?php
// src/AppBundle/Entity/DayOff.php
namespace AppBundle\Entity;

use Roshyo\PlanningBundle\Calendar\Items\Item;

class Employee extends Item
{
  ...
```
