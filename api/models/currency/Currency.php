<?php
declare(strict_types=1);

namespace api\models\currency;

use DateTimeInterface;

class Currency
{

    const ID = 'id';

    public int $id;

    public string $valuteId;

    public int $numCode;

    public string $charCode;

    public string $name;

    public int $nominal;

    public float $value;

    public DateTimeInterface $date;
}
