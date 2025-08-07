<?php

namespace Api\Models;

use Artisan\Services\Doctrine;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "users")]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    private string $email = '';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    private string $password = '';

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $verified = false;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $surname = null;

    #[ORM\Column(type: Types::STRING, length: 3, nullable: true)]
    private ?string $country_code = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: true)]
    private ?string $timezone = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $created_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function setVerified(bool $isVerified): self
    {
        $this->verified = $isVerified;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;
        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->country_code;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->country_code = $countryCode;
        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    /**
     * This method not shows the password
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'surname' => $this->surname,
            'country_code' => $this->country_code,
            'timezone' => $this->timezone,
            'is_verified' => $this->verified,
            'created_at' => $this->created_at->format(DateTimeInterface::ATOM),
        ];
    }

    public static function findOne(array $filter): ?User
    {
        $em = Doctrine::i()->getEntityManager();
        return $em->getRepository(static::class)->findOneBy($filter);
    }

}
