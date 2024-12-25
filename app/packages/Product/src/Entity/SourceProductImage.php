<?php
namespace EcomHelper\Product\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Skeletor\Core\Entity\Timestampable;

#[ORM\Entity]
#[ORM\Table(name: 'sourceProductImage')]
class SourceProductImage
{
    use Timestampable;

    #[ORM\Column(type: Types::STRING)]
    public string $fileName;

    #[ORM\Column(type: Types::STRING)]
    public string $parsedProductId;

    #[ORM\Column(type: Types::INTEGER)]
    public int $main;

}