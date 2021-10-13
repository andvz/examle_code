<?php declare(strict_types=1);

namespace App\Entity\Catalog;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Справочник производителей товара
 *
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\ManufacturerRepository")
 * @UniqueEntity("title", message="Производитель с ками названием уже существует")
 * @UniqueEntity("alias", message="Такой алиас уже существует")
 */
class Manufacturer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank(message="Укажите заголовок")
     */
    public string $title;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank(message="Укажите alias")
     * @Assert\Regex(pattern="/^[a-zA-Z0-9\-]++$/uD", message="Алиас не соответствует формату")
     */
    public string $alias;

    /**
     * @var Collection|ManufacturerCatalogCategory[]
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Catalog\ManufacturerCatalogCategory",
     *     mappedBy="manufacturer",
     *     cascade={"persist"},
     *     orphanRemoval=true,
     * )
     */
    private Collection $manufacturerCatalogCategories;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    public ?string $description;

    /**
     * @ORM\Column(name="keywords", type="string", nullable=true)
     */
    public ?string $keywords;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime_immutable")
     */
    public \DateTimeImmutable $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime_immutable")
     */
    public \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->manufacturerCatalogCategories = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return CatalogCategory[]
     */
    public function getCatalogCategories(): array
    {
        /**
         * @var CatalogCategory[]
         */
        $catalogCategories = [];
        foreach ($this->manufacturerCatalogCategories as $manufacturerCatalogCategory) {
            $catalogCategories[] = $manufacturerCatalogCategory->catalogCategory;
        }

        return $catalogCategories;
    }

    /**
     * @param ManufacturerCatalogCategory[] $newManufacturerCatalogCategories
     */
    public function replaceManufacturerCatalogCategories(array $newManufacturerCatalogCategories): void
    {
        $this->manufacturerCatalogCategories->clear();

        foreach ($newManufacturerCatalogCategories as $newManufacturerCatalogCategory) {
            $this->manufacturerCatalogCategories->add($newManufacturerCatalogCategory);
        }
    }

    /**
     * @param CatalogCategory[] $catalogCategories
     */
    public function addCatalogCategories(array $catalogCategories): void
    {
        foreach ($catalogCategories as $catalogCategory) {
            $this->manufacturerCatalogCategories->add(new ManufacturerCatalogCategory($this, $catalogCategory));
        }
    }
}
