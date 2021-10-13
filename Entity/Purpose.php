<?php declare(strict_types=1);

namespace App\Entity\Catalog;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Назначения
 *
 * @ORM\Entity(repositoryClass="App\Repository\Catalog\PurposeRepository")
 * @UniqueEntity("title", message="Такоe назначение уже существует")
 * @UniqueEntity("alias", message="Такой алиас уже существует")
 */
class Purpose
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
     * @var Collection|PurposeCatalogCategory[]
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Catalog\PurposeCatalogCategory",
     *     mappedBy="purpose",
     *     cascade={"persist"},
     *     orphanRemoval=true,
     * )
     */
    private Collection $purposeCatalogCategories;

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
        $this->purposeCatalogCategories = new ArrayCollection();
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
        $catalogCategories = [];
        /** @var PurposeCatalogCategory $purposeCatalogCategory */
        foreach ($this->purposeCatalogCategories as $purposeCatalogCategory) {
            $catalogCategories[] = $purposeCatalogCategory->catalogCategory;
        }

        return $catalogCategories;
    }

    /**
     * @param CatalogCategory[] $catalogCategories
     */
    public function addCatalogCategories(array $catalogCategories): void
    {
        foreach ($catalogCategories as $catalogCategory) {
            $this->purposeCatalogCategories->add(new PurposeCatalogCategory($this, $catalogCategory));
        }
    }

    /**
     * @param PurposeCatalogCategory[] $newPurposeCatalogCategories
     */
    public function replacePurposeCatalogCategories(array $newPurposeCatalogCategories): void
    {
        $this->purposeCatalogCategories->clear();

        foreach ($newPurposeCatalogCategories as $newPurposeCatalogCategory) {
            $this->purposeCatalogCategories->add($newPurposeCatalogCategory);
        }
    }
}
