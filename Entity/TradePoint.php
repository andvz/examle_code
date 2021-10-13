<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Service\Service;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Торговые точки
 *
 * @ORM\Entity(repositoryClass="App\Repository\TradePointRepository")
 * @UniqueEntity("title", message="Такой заголовок уже используется")
 */
class TradePoint
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Укажите заголовок")
     *
     * @var string
     */
    public $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Укажите адрес точки")
     *
     * @var string
     */
    public $address;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Укажите время работы")
     *
     * @var string
     */
    public $workingHours;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Укажите телефон")
     * @Assert\Regex(pattern="/^((8|\+7)[\- ]?)?(\(?\d{3,4}\)?[\- ]?)?[\d\- ]{7,10}$/", message="Телефон не соответствует формату")
     *
     * @var string
     */
    public $phone;

    /**
     * @var ArrayCollection|Service[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Service\Service")
     * @ORM\JoinTable(name="trade_point_service")
     */
    private $services;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public ?string $content;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public ?string $linkMap;

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
        $this->content = null;
        $this->linkMap = null;
        $this->services = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Service[]
     */
    public function getServices(): array
    {
        return $this->services->toArray();
    }

    public function addService(Service $service): void
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
        }
    }

    public function removeService(Service $service): void
    {
        if ($this->services->contains($service)) {
            $this->services->removeElement($service);
        }
    }
}
