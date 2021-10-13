<?php declare(strict_types=1);

namespace App\Entity\Catalog;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Параметры каталога
 *
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\ManufacturerCatalogCategoryRepository")
 */
class ManufacturerCatalogCategory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Manufacturer", inversedBy="manufacturerCatalogCategories")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    public Manufacturer $manufacturer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\CatalogCategory")
     * @Gedmo\SortableGroup
     */
    public CatalogCategory $catalogCategory;

    /**
     * @ORM\Column(type="smallint")
     * @Gedmo\SortablePosition
     */
    public ?int $position;

    public function __construct(Manufacturer $manufacturer, CatalogCategory $catalogCategory)
    {
        $this->manufacturer = $manufacturer;
        $this->catalogCategory = $catalogCategory;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
