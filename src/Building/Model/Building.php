<?php

namespace App\Building\Model;

use App\Resource\ValueObject\BuildingId;
use App\Resource\ValueObject\BuildingType;

class Building
{
    public function __construct(
        private BuildingId $id,
        private BuildingType $type,
        private int $level
    ){
    }

    public function getId(): BuildingId
    {
        return $this->id;
    }

    public function getType(): BuildingType
    {
        return $this->type;
    }

    public function setType(BuildingType $type): void
    {
        $this->type = $type;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }
}
