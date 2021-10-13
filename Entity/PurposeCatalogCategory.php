<?php declare(strict_types=1);

namespace App\Entity\Catalog;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Параметры каталога
 *
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\PurposeCatalogCategoryRepository")
 */
class PurposeCatalogCategory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\Purpose", inversedBy="purposeCatalogCategories")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    public Purpose $purpose;

    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="App\Entity\Catalog\CatalogCategory")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    public CatalogCategory $catalogCategory;

    /**
     * @ORM\Column(type="smallint")
     * @Gedmo\SortablePosition
     */
    public int $position;

    public function __construct(Purpose $purpose, CatalogCategory $catalogCategory)
    {
        $this->purpose = $purpose;
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
